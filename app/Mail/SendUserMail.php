<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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

        return $mail;
    }

    /**
     * True when the content is already a full HTML page rather than a fragment.
     */
    protected function isCompleteDocument(?string $content): bool
    {
        return $content && preg_match('/<\s*(!doctype|html)\b/i', $content) === 1;
    }
}
