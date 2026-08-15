<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmailSetting;
use App\Models\EmailConfig;
use App\Models\EmailTemplate;
use App\Models\SmsConfig;
use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use App\Models\CronSetting;
use App\Models\Log;
use App\Mail\SendUserMail;
use App\Services\BestBulkSmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class SendBirthdayWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send-wishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday wishes (Email and SMS) to employees whose birthday is today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info('Checking for birthdays on ' . $today->format('d M Y') . ' (' . config('app.timezone') . ')...');

        // In a non-leap year nobody has a 29 February birthday, so greet those
        // employees on 28 February instead - otherwise they are skipped for 4 years.
        $greetLeapDayToday = $today->month === 2 && $today->day === 28 && !$today->isLeapYear();

        $employees = Employee::where('status', 'active')
            ->where(function ($query) use ($today, $greetLeapDayToday) {
                $query->where(function ($q) use ($today) {
                    $q->whereMonth('birthday', $today->month)
                      ->whereDay('birthday', $today->day);
                });

                if ($greetLeapDayToday) {
                    $query->orWhere(function ($q) {
                        $q->whereMonth('birthday', 2)->whereDay('birthday', 29);
                    });
                }
            })
            ->get();

        if ($greetLeapDayToday) {
            $this->line('Non-leap year: 29 February birthdays are included today.');
        }

        $this->touchLastRun();

        if ($employees->isEmpty()) {
            $this->info('No birthdays found today.');
            return;
        }

        $this->info('Found ' . $employees->count() . ' birthday(s) today. Preparing to send wishes...');

        // 1. Email Configuration
        if (config('mail.enabled')) {
            $this->configureEmail();
        } else {
            $this->warn('Outgoing email is disabled (MAIL_ENABLED=false) - sending SMS only.');
        }

        // 2. SMS Configuration
        $smsSetting = SmsSetting::first();
        $smsEnabled = $smsSetting && $smsSetting->status;
        $sms = new BestBulkSmsService();

        // 3. Templates
        $emailTemplate = EmailTemplate::where('template_name', 'Birthday Wish')->first()
            ?? EmailTemplate::where('template_type', 'Birthday')->first();

        $smsTemplate = $this->resolveSmsTemplate($smsSetting);

        if ($smsEnabled && !$smsTemplate) {
            $this->warn('No SMS template found - a default birthday message will be used.');
        }

        // 4. Remaining allowance for today
        $remainingSms   = $this->remainingSmsAllowance($smsSetting);
        $remainingEmail = $this->remainingEmailAllowance();

        foreach ($employees as $employee) {
            // --- Send Email ---
            if (config('mail.enabled') && $employee->email) {
                if ($remainingEmail !== null && $remainingEmail <= 0) {
                    $this->warn("Daily email limit reached - skipping email for {$employee->full_name}.");
                } elseif ($this->alreadyEmailed($employee)) {
                    $this->line("Email already sent to {$employee->full_name} today - skipping.");
                } else {
                    $this->sendBirthdayEmail($employee, $emailTemplate);

                    if ($remainingEmail !== null) {
                        $remainingEmail--;
                    }
                }
            }

            // --- Send SMS ---
            if (!$smsEnabled || !$employee->phone) {
                continue;
            }

            if ($remainingSms !== null && $remainingSms <= 0) {
                $this->warn("Daily SMS limit reached - skipping SMS for {$employee->full_name}.");
                continue;
            }

            if ($this->alreadyTexted($employee, $sms)) {
                $this->line("SMS already sent to {$employee->full_name} today - skipping.");
                continue;
            }

            $this->sendBirthdaySms($employee, $smsTemplate, $sms);

            if ($remainingSms !== null) {
                $remainingSms--;
            }
        }

        $this->info('Birthday wishes process completed.');
    }

    /**
     * The template the admin picked in SMS Settings, with sensible fallbacks.
     */
    private function resolveSmsTemplate($smsSetting)
    {
        if ($smsSetting && $smsSetting->sms_template_id) {
            $template = SmsTemplate::find($smsSetting->sms_template_id);

            if ($template) {
                return $template;
            }
        }

        return SmsTemplate::where('template_name', 'Birthday')->first()
            ?? SmsTemplate::orderBy('id')->first();
    }

    /**
     * How many more SMS may go out today, or null when unlimited.
     */
    private function remainingSmsAllowance($smsSetting): ?int
    {
        if (!$smsSetting || !$smsSetting->daily_limit) {
            return null;
        }

        $sentToday = Log::where('type', 'sms')
            ->where('status', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return max(0, $smsSetting->daily_limit - $sentToday);
    }

    /**
     * How many more emails may go out today, or null when unlimited.
     */
    private function remainingEmailAllowance(): ?int
    {
        $emailSetting = EmailSetting::first();

        if (!$emailSetting || !$emailSetting->daily_limit) {
            return null;
        }

        $sentToday = Log::where('type', 'email')
            ->where('status', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return max(0, $emailSetting->daily_limit - $sentToday);
    }

    /**
     * Guard against a second run of the day sending a duplicate email.
     */
    private function alreadyEmailed($employee): bool
    {
        if (!$employee->email) {
            return false;
        }

        return Log::where('type', 'email')
            ->where('status', 'sent')
            ->where('recipient', $employee->email)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Guard against a second run of the day sending a duplicate greeting.
     */
    private function alreadyTexted($employee, BestBulkSmsService $sms): bool
    {
        $recipients = $sms->normalizeRecipients($employee->phone);

        if (empty($recipients)) {
            return false;
        }

        return Log::where('type', 'sms')
            ->where('status', 'sent')
            ->whereIn('recipient', $recipients)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Record that the scheduler fired today.
     */
    private function touchLastRun()
    {
        try {
            $cronSetting = CronSetting::first();

            if ($cronSetting) {
                $cronSetting->update(['last_run' => Carbon::now()]);
            }
        } catch (\Throwable $e) {
            $this->warn('Could not update last run time: ' . $e->getMessage());
        }
    }

    private function configureEmail()
    {
        $emailConfig = EmailConfig::first();
        $emailSetting = EmailSetting::first();
        $emailEnabled = $emailSetting && $emailSetting->status;

        if ($emailEnabled && $emailConfig) {
            Config::set('mail.mailers.smtp.host', $emailConfig->smtp_host);
            Config::set('mail.mailers.smtp.port', $emailConfig->smtp_port);
            Config::set('mail.mailers.smtp.username', $emailConfig->smtp_username);
            Config::set('mail.mailers.smtp.password', $emailConfig->smtp_password);
            Config::set('mail.mailers.smtp.encryption', $emailConfig->encryption);
            Config::set('mail.from.address', $emailSetting->sender_email ?? 'noreply@example.com');
            Config::set('mail.from.name', $emailSetting->sender_name ?? 'Birthday Manager');

            // Discard any mailer already built from the previous settings.
            Mail::purge('smtp');
        }
    }

    private function sendBirthdayEmail($employee, $template)
    {
        if (!config('mail.enabled')) {
            return;
        }

        $emailSetting = EmailSetting::first();
        if (!$emailSetting || !$emailSetting->status || !$employee->email) {
            return;
        }

        $subject = $template ? $this->replacePlaceholders($template->subject, $employee) : "Happy Birthday {$employee->full_name}!";
        $content = $template ? $this->replacePlaceholders($template->content, $employee) : "Dear {$employee->full_name}, Wishing you a very happy birthday!";

        try {
            Mail::to($employee->email)->send(new SendUserMail([
                'subject' => $subject,
                'content' => $content
            ]));
            $this->logAction('email', $employee->email, $subject, $content, 'sent');
            $this->info("Email sent to {$employee->full_name} ({$employee->email})");
        } catch (\Exception $e) {
            $this->logAction('email', $employee->email, $subject, $content, 'failed');
            $this->error("Failed to send email to {$employee->full_name}: " . $e->getMessage());
        }
    }

    private function sendBirthdaySms($employee, $template, $sms)
    {
        $smsMessage = $template ? $this->replacePlaceholders($template->message, $employee) : "Happy Birthday {$employee->full_name}! Wishing you a great day ahead.";

        // Log the number in the exact form it was sent, so re-runs can detect duplicates.
        $recipient = $sms->normalizeRecipients($employee->phone)[0] ?? $employee->phone;

        try {
            $response = $sms->sendSMS($employee->phone, $smsMessage);
            $status = BestBulkSmsService::wasSuccessful($response) ? 'sent' : 'failed';

            $this->logAction('sms', $recipient, 'Birthday Wish', $smsMessage, $status);
            
            if ($status == 'sent') {
                $reference = $response['sms_message_id'] ?? 'n/a';
                $this->info("SMS sent to {$employee->full_name} ({$employee->phone}) [message id: {$reference}]");
            } else {
                $this->error("Failed to send SMS to {$employee->full_name}: " . BestBulkSmsService::errorMessage($response));
            }
        } catch (\Exception $e) {
            $this->logAction('sms', $recipient, 'Birthday Wish', $smsMessage, 'failed');
            $this->error("Failed to send SMS to {$employee->full_name}: " . $e->getMessage());
        }
    }

    private function replacePlaceholders($text, $employee)
    {
        try {
            $birthday = Carbon::parse($employee->birthday);
        } catch (\Throwable $e) {
            $birthday = null;
        }

        $values = [
            'full_name'     => $employee->full_name,
            'employee_name' => $employee->full_name,
            'name'          => $employee->full_name,
            'email'         => $employee->email,
            'phone'         => $employee->phone,
            'department'    => $employee->department,
            'designation'   => $employee->designation,
            'birthday'      => $employee->birthday,
            'birthday_date' => $birthday ? $birthday->format('jS F') : $employee->birthday,
            'company_name'  => config('app.name'),
        ];

        // Templates in the app use both {name} and {{ name }} styles - support each.
        $placeholders = [];

        foreach ($values as $key => $value) {
            $value = (string) $value;
            $placeholders['{{ ' . $key . ' }}'] = $value;
            $placeholders['{{' . $key . '}}']   = $value;
            $placeholders['{' . $key . '}']     = $value;
        }

        return strtr($text, $placeholders);
    }

    private function logAction($type, $recipient, $subject, $message, $status)
    {
        Log::create([
            'type' => $type,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'status' => $status
        ]);
    }
}
