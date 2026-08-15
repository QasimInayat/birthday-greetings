<?php

namespace App\Jobs;

use App\Mail\SendUserMail;
use App\Models\EmailConfig;
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

        // 🔹 Fetch SMTP settings from database
        $emailConfig = EmailConfig::first();

        if (!$emailConfig) {
            throw new \RuntimeException('SMTP configuration has not been saved yet.');
        }

        // 🔹 Dynamically configure mail settings
        Config::set('mail.mailers.smtp.host', $emailConfig->smtp_host);
        Config::set('mail.mailers.smtp.port', $emailConfig->smtp_port);
        Config::set('mail.mailers.smtp.username', $emailConfig->smtp_username);
        Config::set('mail.mailers.smtp.password', $emailConfig->smtp_password);
        Config::set('mail.mailers.smtp.encryption', $emailConfig->encryption);
        $emailSetting = \App\Models\EmailSetting::first();
        Config::set('mail.from.address', $emailSetting->sender_email ?? 'noreply@example.com');
        Config::set('mail.from.name', $emailSetting->sender_name ?? config('app.name'));

        // 🔹 Drop any mailer already built with the old settings
        Mail::purge('smtp');

        // 🔹 Send mail
        Mail::to($this->details['email'])->send(new SendUserMail($this->details));
    }
}
