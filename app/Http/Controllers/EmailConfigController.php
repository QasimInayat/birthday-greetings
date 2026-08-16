<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\EmailSetting;
use Illuminate\Http\Request;

class EmailConfigController extends Controller
{
    /**
     * SMTP settings are managed in the .env file, not the database.
     * This page shows what the application is currently using.
     */
    public function index()
    {
        $smtp = config('mail.mailers.smtp');

        return view('email_config.index', [
            'host'       => $smtp['host'] ?? null,
            'port'       => $smtp['port'] ?? null,
            'username'   => $smtp['username'] ?? null,
            'encryption' => $smtp['encryption'] ?? null,
            'hasPassword' => filled($smtp['password'] ?? null),
            'fromAddress' => config('mail.from.address'),
            'fromName'    => config('mail.from.name'),
            'mailEnabled' => (bool) config('mail.enabled'),
        ]);
    }

    // SMTP Test Button - the page expects JSON back
    public function test()
    {
        if (!config('mail.enabled')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Outgoing email is currently disabled. Set MAIL_ENABLED=true in the .env file.',
            ], 422);
        }

        if (!config('mail.mailers.smtp.host')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'MAIL_HOST is not set in the .env file.',
            ], 422);
        }

        $recipient = optional(EmailSetting::first())->sender_email
            ?? config('mail.from.address');

        if (!$recipient) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Set a sender email in Email Settings before testing.',
            ], 422);
        }

        $details = [
            'email'   => $recipient,
            'subject' => 'SMTP test from ' . config('app.name'),
            'title'   => 'SMTP test successful',
            'body'    => 'This email was sent using the SMTP settings in your .env file.',
        ];

        try {
            dispatch(new SendEmailJob($details));
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'SMTP test failed: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Test email sent to ' . $recipient . '.',
        ]);
    }
}
