<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class SendUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        $content = $this->details['content'] ?? null;

        $mail = $this->subject($this->details['subject'] ?? config('app.name'))
            ->with([
                'details' => $this->details,
                'title'   => $this->details['title'] ?? null,
                'body'    => $content ?: nl2br(e($this->details['body'] ?? '')),
            ]);

        if ($this->isCompleteDocument($content)) {
            // A template that brings its own <html> owns its design - send it as is.
            $mail->view('emails.raw');
        } else {
            // Everything else gets Laravel's branded layout instead of bare text.
            $mail->markdown('emails.branded');
        }

        // Plain-text twin: mail without one is treated as a spam signal.
        $mail->text('emails.email_template_plain');

        if ($replyTo = config('mail.reply_to.address')) {
            $mail->replyTo($replyTo, config('mail.reply_to.name') ?: config('mail.from.name'));
        }

        $this->applyDeliverabilityHeaders($mail);

        return $mail;
    }

    /**
     * Headers that push mail towards the Inbox rather than Gmail's Promotions tab.
     *
     * The biggest single signal is Mailgun's open/click tracking: it rewrites
     * every link to a tracking domain and injects a tracking pixel, which reads
     * as marketing. These headers switch that off per message.
     */
    protected function applyDeliverabilityHeaders(Mailable $mail): void
    {
        if (!config('mail.deliverability.disable_tracking', true)) {
            return;
        }

        $mail->withSymfonyMessage(function (Email $message) {
            $headers = $message->getHeaders();

            // Mailgun reads these from SMTP submissions.
            $headers->addTextHeader('X-Mailgun-Track', 'no');
            $headers->addTextHeader('X-Mailgun-Track-Clicks', 'no');
            $headers->addTextHeader('X-Mailgun-Track-Opens', 'no');

            // Identifies the mail as transactional rather than a campaign.
            $headers->addTextHeader('Auto-Submitted', 'auto-generated');
        });
    }

    /**
     * True when the content is already a full HTML page rather than a fragment.
     */
    protected function isCompleteDocument(?string $content): bool
    {
        return $content && preg_match('/<\s*(!doctype|html)\b/i', $content) === 1;
    }
}
