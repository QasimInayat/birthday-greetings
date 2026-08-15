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

        return view('sms_settings.index', compact('setting', 'templates'));
    }

    // Save or Update SMS Settings
    public function store(Request $request)
    {
        $request->validate([
            'daily_limit'     => 'required|integer|min:1',
            'sender_id'       => 'required|string|max:20',
            'sms_template_id' => 'required|exists:sms_templates,id',
        ]);

        SmsSetting::updateOrCreate(
            ['id' => 1], // Always single record
            [
                'daily_limit'     => $request->daily_limit,
                'sender_id'       => $request->sender_id,
                'sms_template_id' => $request->sms_template_id,
                'status'          => $request->has('status') ? 1 : 0,
            ]
        );

        return redirect()->route('sms-settings.index')
            ->with('success', 'SMS settings updated successfully.');
    }
}
