<?php

namespace App\Http\Controllers;

use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsSettingController extends Controller
{
    // Show SMS Settings Page
    public function index()
    {
        $setting   = SmsSetting::first();
        $templates = SmsTemplate::orderBy('template_name')->get();

        // Resolve exactly what the cron would send, so the page can state it
        // outright instead of leaving the admin to guess.
        $active = $setting && $setting->sms_template_id
            ? SmsTemplate::find($setting->sms_template_id)
            : null;

        $isFallback = false;

        if (!$active) {
            $active = SmsTemplate::where('template_name', 'Birthday')->first()
                ?? SmsTemplate::orderBy('id')->first();
            $isFallback = (bool) $active;
        }

        return view('sms_settings.index', compact('setting', 'templates', 'active', 'isFallback'));
    }

    // Save or Update SMS Settings
    public function store(Request $request)
    {
        $request->validate([
            'daily_limit'     => 'required|integer|min:1',
            'sender_id'       => 'required|string|max:20',
            'sms_template_id' => 'required|exists:sms_templates,id',
        ]);

        // Update whichever row exists rather than assuming id 1, so a settings
        // row created with a different id can never drift out of sync.
        $setting = SmsSetting::first() ?: new SmsSetting();

        $setting->fill([
            'daily_limit'     => $request->daily_limit,
            'sender_id'       => trim($request->sender_id),
            'sms_template_id' => $request->sms_template_id,
            'status'          => $request->has('status') ? 1 : 0,
        ])->save();

        $template = SmsTemplate::find($setting->sms_template_id);

        return redirect()->route('sms-settings.index')
            ->with('success', 'SMS settings saved. Birthday messages will now use the template "'
                . ($template->template_name ?? 'unknown') . '".');
    }
}
