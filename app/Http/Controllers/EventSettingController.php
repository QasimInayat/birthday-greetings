<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\EventSetting;
use App\Models\SmsTemplate;
use App\Services\EventNotifier;
use Illuminate\Http\Request;

class EventSettingController extends Controller
{
    public function index()
    {
        $types = config('templates.types');
        $rows = [];

        foreach ($types as $key => $label) {
            $setting = EventSetting::forType($key);

            $rows[] = [
                'key'          => $key,
                'label'        => $label,
                'trigger'      => config('templates.triggers')[$key] ?? '',
                'automated'    => in_array($key, config('templates.automated', []), true),
                'setting'      => $setting,
                'smsTemplate'  => SmsTemplate::defaultFor($key),
                'mailTemplate' => EmailTemplate::defaultFor($key),
            ];
        }

        return view('event_settings.index', [
            'rows'           => $rows,
            'missingJoinDate' => Employee::where('status', 'active')->whereNull('date_of_joining')->count(),
            'activeCount'    => Employee::where('status', 'active')->count(),
            // Anniversaries need a joining date at least a year old.
            'anniversaryEligible' => Employee::where('status', 'active')
                ->whereNotNull('date_of_joining')
                ->whereDate('date_of_joining', '<=', now()->subYear())
                ->count(),
            'employees' => Employee::where('status', 'active')->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    /**
     * Send one event to one employee right now, ignoring date matching and the
     * once-per-day guard, so any message type can be verified on demand.
     */
    public function sendTest(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, config('templates.types')), 404);

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);

        $notifier = new EventNotifier();
        $notifier->testMode = true;

        $delivered = $notifier->notify($employee, $type, true);

        $label = EventSetting::forType($type)->label();

        if ($delivered) {
            return redirect()->route('event-settings.index')
                ->with('success', "Test {$label} sent to {$employee->full_name} via "
                    . implode(' and ', $delivered) . '. Check Delivery Logs — it is marked [TEST].');
        }

        $why = $notifier->errors ?: $notifier->skipped;

        return redirect()->route('event-settings.index')
            ->with('error', "Test {$label} to {$employee->full_name} sent nothing. "
                . ($why ? implode('; ', array_unique($why)) : 'No channel was available.'));
    }

    public function update(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, config('templates.types')), 404);

        $setting = EventSetting::forType($type);

        $setting->update([
            'status'          => $request->boolean('status'),
            'send_sms'        => $request->boolean('send_sms'),
            'send_email'      => $request->boolean('send_email'),
            'milestones_only' => $request->boolean('milestones_only'),
        ]);

        $label = $setting->label();

        if ($setting->status && !$setting->send_sms && !$setting->send_email) {
            return redirect()->route('event-settings.index')
                ->with('error', $label . ' is enabled but neither SMS nor email is selected, so nothing will send.');
        }

        return redirect()->route('event-settings.index')
            ->with('success', $label . ' ' . ($setting->status ? 'enabled.' : 'disabled.'));
    }
}
