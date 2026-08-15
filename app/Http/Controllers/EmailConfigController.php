<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\EmailConfig;
use Illuminate\Http\Request;

class EmailConfigController extends Controller
{
    // Show Config Page
    public function index()
    {
        $config = EmailConfig::first();
        return view('email_config.index', compact('config'));
    }

    // Save or Update Config
    public function update(Request $request)
    {
        $existing = EmailConfig::first();

        $request->validate([
            'smtp_host'     => 'required',
            'smtp_port'     => 'required|numeric',
            'smtp_username' => 'required',
            // Only required the first time - a blank field keeps the stored password.
            'smtp_password' => ($existing && $existing->smtp_password) ? 'nullable|string' : 'required|string',
            'encryption'    => 'required|in:tls,ssl,none',
        ]);

        $data = [
            'smtp_host'     => $request->smtp_host,
            'smtp_port'     => $request->smtp_port,
            'smtp_username' => $request->smtp_username,
            'encryption'    => $request->encryption,
        ];

        if (filled($request->smtp_password)) {
            $data['smtp_password'] = $request->smtp_password;
        }

        EmailConfig::updateOrCreate(['id' => 1], $data); // Always keep single record

        return redirect()->route('email-config.index')
            ->with('success', 'SMTP configuration saved successfully.');
    }

    // SMTP Test Button - the page expects JSON back
    public function test()
    {
        if (!config('mail.enabled')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Outgoing email is currently disabled. Set MAIL_ENABLED=true in the .env file to send email.',
            ], 422);
        }

        if (!EmailConfig::first()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Save the SMTP configuration before testing it.',
            ], 422);
        }

        $recipient = optional(\App\Models\EmailSetting::first())->sender_email
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
            'body'    => 'This email was sent using the SMTP settings stored in the database.',
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
            'message' => 'Test email dispatched to ' . $details['email'] . '.',
        ]);
    }
}
