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
        $mail = $this->subject($this->details['subject'])
                     ->view('emails.email_template')
                     // A plain-text alternative alongside the HTML: mail without
                     // one is treated as a spam signal by most providers.
                     ->text('emails.email_template_plain')
                     ->with('details', $this->details);

        if ($replyTo = config('mail.reply_to.address')) {
            $mail->replyTo($replyTo, config('mail.reply_to.name') ?: config('mail.from.name'));
        }

        return $mail;
    }
}
