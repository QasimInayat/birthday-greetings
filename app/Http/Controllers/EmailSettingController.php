<?php

namespace App\Http\Controllers;

use App\Models\EmailSetting;
use Illuminate\Http\Request;

class EmailSettingController extends Controller
{
    // Show Settings Page
    public function index()
    {
        $setting = EmailSetting::first();
        return view('email_settings.index', compact('setting'));
    }

    // Save or Update Settings
    public function store(Request $request)
    {
        $request->validate([
            'daily_limit'  => 'required|integer|min:1',
            'sender_name'  => 'required|string|max:255',
            'sender_email' => 'required|email',
        ]);

        EmailSetting::updateOrCreate(
            ['id' => 1], // Only one record
            [
                'daily_limit'  => $request->daily_limit,
                'sender_name'  => $request->sender_name,
                'sender_email' => $request->sender_email,
                'status'       => $request->has('status') ? 1 : 0,
            ]
        );

        return redirect()->route('email-settings.index')
            ->with('success', 'Email settings updated successfully.');
    }
}
