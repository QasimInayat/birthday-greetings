<?php

namespace App\Services;

use App\Mail\SendUserMail;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\EventSetting;
use App\Models\Log;
use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * One place where every employee event is turned into an SMS and/or email.
 *
 * Scheduled events (birthday, anniversary), record-triggered events
 * (welcome, farewell) and manual sends (general) all route through here, so
 * templating, duplicate protection, limits and logging behave identically.
 */
class EventNotifier
{
    protected BestBulkSmsService $sms;

    /** Messages sent during this run, per channel. */
    public array $sent = ['sms' => 0, 'email' => 0];
    public array $skipped = [];

    /** Real failure reasons, so callers can show them instead of a silent miss. */
    public array $errors = [];
    public ?string $lastError = null;

    /** Test sends are logged distinctly so they cannot be mistaken for live ones. */
    public bool $testMode = false;

    public function __construct(?BestBulkSmsService $sms = null)
    {
        $this->sms = $sms ?: new BestBulkSmsService();
    }

    /**
     * Send one event to one employee. Returns the channels actually delivered.
     *
     * @param  bool  $force  ignore the once-per-day guard (manual sends)
     */
    public function notify(Employee $employee, string $eventType, bool $force = false): array
    {
        $setting = EventSetting::forType($eventType);
        $delivered = [];

        if (!$force && !$setting->isEnabled()) {
            $this->skipped[] = "{$employee->full_name}: {$eventType} is switched off";
            return $delivered;
        }

        if ($setting->send_sms && $this->sendSms($employee, $eventType, $force)) {
            $delivered[] = 'sms';
        }

        if ($setting->send_email && $this->sendEmail($employee, $eventType, $force)) {
            $delivered[] = 'email';
        }

        return $delivered;
    }

    /**
     * Send an ad-hoc message now, bypassing templates and the daily guard.
     * Used by Broadcast for the "general" type.
     *
     * @return array{sms: bool, email: bool}
     */
    public function broadcast(Employee $employee, string $body, string $subject, string $channel): array
    {
        $result = ['sms' => false, 'email' => false];

        if ($channel === 'sms' || $channel === 'both') {
            $result['sms'] = $this->deliverSms($employee, 'general', $subject, $body);
        }

        if ($channel === 'email' || $channel === 'both') {
            $result['email'] = $this->deliverEmail($employee, 'general', $subject, '<p>'
                . nl2br(e($body)) . '</p>');
        }

        return $result;
    }

    /* ------------------------------------------------------------------ SMS */

    protected function sendSms(Employee $employee, string $eventType, bool $force): bool
    {
        $smsSetting = SmsSetting::first();

        if (!$smsSetting || !$smsSetting->status || !$employee->phone) {
            return false;
        }

        $recipient = $this->sms->normalizeRecipients($employee->phone)[0] ?? null;

        if (!$recipient) {
            $this->skipped[] = "{$employee->full_name}: unusable phone number";
            return false;
        }

        if (!$force && $this->alreadySent('sms', $recipient, $eventType)) {
            $this->skipped[] = "{$employee->full_name}: {$eventType} SMS already sent today";
            return false;
        }

        if ($this->overDailyLimit('sms', $smsSetting->daily_limit)) {
            $this->skipped[] = "daily SMS limit reached";
            return false;
        }

        $template = SmsTemplate::defaultFor($eventType);
        $message = $template
            ? $this->render($template->message, $employee, $eventType)
            : $this->fallbackText($employee, $eventType);

        return $this->deliverSms($employee, $eventType, $this->subjectFor($eventType), $message);
    }

    /** Actually put an SMS on the wire and record the outcome. */
    protected function deliverSms(Employee $employee, string $eventType, string $subject, string $message): bool
    {
        $recipient = $this->sms->normalizeRecipients($employee->phone)[0] ?? null;

        if (!$recipient) {
            return false;
        }

        try {
            $response = $this->sms->sendSMS($employee->phone, $message);
            $ok = BestBulkSmsService::wasSuccessful($response);

            $this->log('sms', $eventType, $recipient, $subject, $message, $ok ? 'sent' : 'failed');

            if ($ok) {
                $this->sent['sms']++;
                return true;
            }

            $reason = BestBulkSmsService::errorMessage($response);
            $this->errors[] = "{$employee->full_name}: {$reason}";
            $this->lastError = $reason;

            $this->safeLog('Event SMS failed', [
                'employee' => $employee->full_name,
                'event'    => $eventType,
                'reason'   => $reason,
            ]);
        } catch (\Throwable $e) {
            $this->log('sms', $eventType, $recipient, $subject, $message, 'failed');
            $this->errors[] = "{$employee->full_name}: " . $e->getMessage();
            $this->lastError = $e->getMessage();

            $this->safeLog('Event SMS failed', [
                'employee' => $employee->full_name, 'event' => $eventType, 'reason' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /* ---------------------------------------------------------------- Email */

    protected function sendEmail(Employee $employee, string $eventType, bool $force): bool
    {
        if (!config('mail.enabled') || !$employee->email) {
            return false;
        }

        $emailSetting = EmailSetting::first();

        if (!$emailSetting || !$emailSetting->status) {
            return false;
        }

        if (!$force && $this->alreadySent('email', $employee->email, $eventType)) {
            $this->skipped[] = "{$employee->full_name}: {$eventType} email already sent today";
            return false;
        }

        if ($this->overDailyLimit('email', $emailSetting->daily_limit)) {
            $this->skipped[] = "daily email limit reached";
            return false;
        }

        $template = EmailTemplate::defaultFor($eventType);

        $subject = $template
            ? $this->render($template->subject, $employee, $eventType)
            : $this->subjectFor($eventType);

        $content = $template
            ? $this->render($template->content, $employee, $eventType)
            : '<p>' . e($this->fallbackText($employee, $eventType)) . '</p>';

        return $this->deliverEmail($employee, $eventType, $subject, $content);
    }

    /**
     * Apply the visible sender identity from Email Settings.
     * Every send goes through here - the SMTP connection itself stays in .env.
     */
    protected function applySenderIdentity(): void
    {
        $emailSetting = EmailSetting::first();

        if ($emailSetting && $emailSetting->sender_email) {
            Config::set('mail.from.address', $emailSetting->sender_email);
        }

        if ($emailSetting && $emailSetting->sender_name) {
            Config::set('mail.from.name', $emailSetting->sender_name);
        }
    }

    /** Actually send an email and record the outcome. */
    protected function deliverEmail(Employee $employee, string $eventType, string $subject, string $content): bool
    {
        if (!config('mail.enabled')) {
            $this->skipped[] = 'outgoing email is switched off (MAIL_ENABLED)';
            return false;
        }

        if (!$employee->email) {
            $this->skipped[] = "{$employee->full_name}: no email address";
            return false;
        }

        $emailSetting = EmailSetting::first();

        if (!$emailSetting || !$emailSetting->status) {
            $this->skipped[] = 'email notifications are disabled in Email Settings';
            return false;
        }

        // Was previously only applied on the scheduled path, so broadcast sent
        // with whatever MAIL_FROM_ADDRESS happened to be - and Mailgun rejected it.
        $this->applySenderIdentity();

        try {
            Mail::to($employee->email)->send(new SendUserMail([
                'subject' => $subject,
                'content' => $content,
            ]));

            $this->log('email', $eventType, $employee->email, $subject, $content, 'sent');
            $this->sent['email']++;

            return true;
        } catch (\Throwable $e) {
            $this->log('email', $eventType, $employee->email, $subject, $content, 'failed');

            // Surface the real reason instead of failing silently.
            $this->errors[] = "{$employee->full_name}: " . $e->getMessage();
            $this->lastError = $e->getMessage();

            $this->safeLog('Event email failed', [
                'employee' => $employee->full_name, 'event' => $eventType, 'reason' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /* ------------------------------------------------------------- Internals */

    /** Guard against a repeat of the same event, to the same person, today. */
    protected function alreadySent(string $channel, string $recipient, string $eventType): bool
    {
        return Log::where('type', $channel)
            ->where('status', 'sent')
            ->where('recipient', $recipient)
            ->where('event_type', $eventType)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    protected function overDailyLimit(string $channel, $limit): bool
    {
        if (!$limit) {
            return false;
        }

        $today = Log::where('type', $channel)
            ->where('status', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $today >= $limit;
    }

    /**
     * Write to the application log without ever throwing.
     *
     * If storage/logs is not writable - typically because artisan was run as
     * root and took ownership of laravel.log - Laravel throws while logging.
     * That turned a single failed send into a 500 for the whole request.
     */
    protected function safeLog(string $message, array $context = []): void
    {
        try {
            \Illuminate\Support\Facades\Log::error($message, $context);
        } catch (\Throwable $e) {
            // Logging is diagnostics, never a reason to fail the operation.
        }
    }

    protected function log($channel, $eventType, $recipient, $subject, $message, $status): void
    {
        Log::create([
            'type'       => $channel,
            'event_type' => $eventType,
            'recipient'  => $recipient,
            'subject'    => $this->testMode ? '[TEST] ' . $subject : $subject,
            'message'    => $message,
            'status'     => $status,
        ]);
    }

    public function subjectFor(string $eventType): string
    {
        return [
            'birthday'    => 'Birthday Wish',
            'anniversary' => 'Work Anniversary',
            'welcome'     => 'Welcome',
            'farewell'    => 'Farewell',
            'general'     => 'Message',
        ][$eventType] ?? ucfirst($eventType);
    }

    protected function fallbackText(Employee $employee, string $eventType): string
    {
        return [
            'birthday'    => "Happy Birthday {$employee->full_name}! Wishing you a great day ahead.",
            'anniversary' => "Congratulations {$employee->full_name} on your work anniversary!",
            'welcome'     => "Welcome aboard, {$employee->full_name}!",
            'farewell'    => "Thank you for everything, {$employee->full_name}. We wish you well.",
        ][$eventType] ?? "Hello {$employee->full_name}.";
    }

    /**
     * Replace merge tags. Supports {tag}, {{tag}} and {{ tag }}.
     */
    public function render(string $text, Employee $employee, string $eventType = 'general'): string
    {
        try {
            $birthday = $employee->birthday ? Carbon::parse($employee->birthday) : null;
        } catch (\Throwable $e) {
            $birthday = null;
        }

        $years = $employee->yearsOfService();

        $values = [
            'full_name'        => $employee->full_name,
            'employee_name'    => $employee->full_name,
            'name'             => $employee->full_name,
            'email'            => $employee->email,
            'phone'            => $employee->phone,
            'department'       => $employee->department,
            'designation'      => $employee->designation,
            'birthday'         => $employee->birthday,
            'birthday_date'    => $birthday ? $birthday->format('jS F') : '',
            'join_date'        => $employee->date_of_joining ? $employee->date_of_joining->format('jS F Y') : '',
            'years_of_service' => $years !== null ? (string) $years : '',
            'company_name'     => config('app.name'),
            // So custom HTML templates can embed the logo too.
            'logo_url'         => \App\Support\Branding::logoUrl() ?: '',
        ];

        $replacements = [];

        foreach ($values as $key => $value) {
            $value = (string) $value;
            $replacements['{{ ' . $key . ' }}'] = $value;
            $replacements['{{' . $key . '}}']   = $value;
            $replacements['{' . $key . '}']     = $value;
        }

        return strtr($text, $replacements);
    }
}
