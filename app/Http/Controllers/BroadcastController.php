<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SmsTemplate;
use App\Services\EventNotifier;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function index()
    {
        return view('broadcast.index', [
            'departments' => Employee::where('status', 'active')
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'templates'   => SmsTemplate::where('template_type', 'general')->orderBy('template_name')->get(),
            'activeCount' => Employee::where('status', 'active')->count(),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'audience'   => 'required|in:all,department',
            'department' => 'required_if:audience,department|nullable|string',
            'channel'    => 'required|in:sms,email,both',
            'message'    => 'required|string|max:1000',
            'subject'    => 'nullable|string|max:255',
            'confirm'    => 'accepted',
        ], [
            'confirm.accepted' => 'Tick the confirmation box before sending.',
        ]);

        // Optional fields are absent from $validated when not submitted.
        $department = $validated['department'] ?? null;
        $subjectTemplate = $validated['subject'] ?? null;

        $employees = Employee::where('status', 'active')
            ->when($validated['audience'] === 'department',
                fn ($q) => $q->where('department', $department))
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'No active employees match that audience.')->withInput();
        }

        $notifier = new EventNotifier();
        $sms = 0;
        $email = 0;
        $failed = 0;

        foreach ($employees as $employee) {
            $body = $notifier->render($validated['message'], $employee, 'general');
            $subject = $subjectTemplate
                ? $notifier->render($subjectTemplate, $employee, 'general')
                : 'Message from ' . config('app.name');

            $result = $notifier->broadcast($employee, $body, $subject, $validated['channel']);

            $sms   += $result['sms'] ? 1 : 0;
            $email += $result['email'] ? 1 : 0;

            if (!$result['sms'] && !$result['email']) {
                $failed++;
            }
        }

        $summary = "Broadcast finished: {$sms} SMS and {$email} email sent to {$employees->count()} employee(s).";

        if ($failed) {
            $summary .= " {$failed} received nothing — check Delivery Logs.";
        }

        return redirect()->route('broadcast.index')->with('success', $summary);
    }
}
