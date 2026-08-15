<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmailSetting;
use App\Models\EmailConfig;
use App\Models\User;
use App\Mail\BirthdaySummaryMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class SendBirthdaySummaryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:birthday-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily summary report of today\'s and upcoming birthdays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('mail.enabled')) {
            $this->warn('Outgoing email is disabled (MAIL_ENABLED=false) - summary report skipped.');
            return;
        }

        $this->info('Starting birthday summary report generation...');

        // 1. Fetch Today's Birthdays
        $today = Carbon::today();
        $todaysBirthdays = Employee::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        // 2. Fetch Upcoming Birthdays (Next 7 Days)
        $upcomingBirthdays = collect();
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            $birthdays = Employee::whereMonth('birthday', $date->month)
                ->whereDay('birthday', $date->day)
                ->get();
            $upcomingBirthdays = $upcomingBirthdays->merge($birthdays);
        }

        if ($todaysBirthdays->isEmpty() && $upcomingBirthdays->isEmpty()) {
            $this->info('No birthdays found for today or the next 7 days. Skipping email.');
            // return; // Uncomment if you don't want to send empty reports
        }

        // 3. Configure Mail dynamically (based on SendEmailJob logic)
        $emailConfig = EmailConfig::first();
        if (!$emailConfig) {
            $this->error('Email configuration (SMTP) not found in database.');
            return;
        }

        $emailSetting = EmailSetting::first();
        if (!$emailSetting || !$emailSetting->status) {
            $this->warn('Email settings are disabled or not found.');
            return;
        }

        Config::set('mail.mailers.smtp.host', $emailConfig->smtp_host);
        Config::set('mail.mailers.smtp.port', $emailConfig->smtp_port);
        Config::set('mail.mailers.smtp.username', $emailConfig->smtp_username);
        Config::set('mail.mailers.smtp.password', $emailConfig->smtp_password);
        Config::set('mail.mailers.smtp.encryption', $emailConfig->encryption);
        Config::set('mail.from.address', $emailSetting->sender_email ?? 'noreply@example.com');
        Config::set('mail.from.name', $emailSetting->sender_name ?? 'Birthday Manager');

        // Discard any mailer already built from the previous settings.
        Mail::purge('smtp');

        // 4. Determine Recipient (Send to the first user/admin)
        $admin = User::first();
        $recipient = $admin ? $admin->email : ($emailSetting->sender_email ?? null);

        if (!$recipient) {
            $this->error('No recipient email found (no users or sender email).');
            return;
        }

        // 5. Send Email
        try {
            Mail::to($recipient)->send(new BirthdaySummaryMail($todaysBirthdays, $upcomingBirthdays));
            $this->info("Birthday summary report sent successfully to {$recipient}!");
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
        }
    }
}
