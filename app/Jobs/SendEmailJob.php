<?php

namespace App\Jobs;

use App\Mail\SendUserMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle(): void
    {
        // 🔹 Outgoing email master switch
        if (!config('mail.enabled')) {
            return;
        }

        // 🔹 SMTP connection comes from .env via config/mail.php - not the database.
        if (!config('mail.mailers.smtp.host')) {
            throw new \RuntimeException('MAIL_HOST is not set in the .env file.');
        }

        // 🔹 Only the visible sender identity comes from Email Settings
        $emailSetting = \App\Models\EmailSetting::first();

        if ($emailSetting && $emailSetting->sender_email) {
            Config::set('mail.from.address', $emailSetting->sender_email);
        }

        if ($emailSetting && $emailSetting->sender_name) {
            Config::set('mail.from.name', $emailSetting->sender_name);
        }

        // 🔹 Send mail
        Mail::to($this->details['email'])->send(new SendUserMail($this->details));
    }
}
