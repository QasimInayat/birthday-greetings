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
        $setting = SmsSetting::first();

        // Only birthday templates can be the birthday message.
        $templates = SmsTemplate::where('template_type', 'birthday')
            ->orderBy('template_name')
            ->get();

        // The default flag on the template is the single source of truth.
        $active = SmsTemplate::where('template_type', 'birthday')->where('is_default', true)->first();
        $isFallback = false;

        if (!$active) {
            $active = SmsTemplate::where('template_type', 'birthday')->orderBy('id')->first();
            $isFallback = (bool) $active;
        }

        return view('sms_settings.index', compact('setting', 'templates', 'active', 'isFallback'));
    }

    // Save or Update SMS Settings
    public function store(Request $request)
    {
        // The template is only required once at least one Birthday template exists,
        // otherwise the other settings on this page could never be saved.
        $hasBirthdayTemplates = SmsTemplate::where('template_type', 'birthday')->exists();

        $request->validate([
            'daily_limit'     => 'required|integer|min:1',
            'sender_id'       => 'required|string|max:20',
            'sms_template_id' => ($hasBirthdayTemplates ? 'required' : 'nullable') . '|exists:sms_templates,id',
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

        // Choosing here is the same act as flagging the template default, so the
        // templates list and this page can never disagree.
        $template = SmsTemplate::find($setting->sms_template_id);

        if ($template) {
            $template->makeDefault();
        }

        return redirect()->route('sms-settings.index')
            ->with('success', 'SMS settings saved. Birthday messages will now use the template "'
                . ($template->template_name ?? 'unknown') . '".');
    }
}
