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

        return view('email_settings.index', [
            'setting'          => $setting,
            'logoUrl'          => \App\Support\Branding::logoUrl(),
            'usingDefaultLogo' => \App\Support\Branding::usingDefaultLogo(),
        ]);
    }

    // Save or Update Settings
    public function store(Request $request)
    {
        $request->validate([
            'daily_limit'  => 'required|integer|min:1',
            'sender_name'  => 'required|string|max:255',
            'sender_email' => 'required|email',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:1024',
        ], [
            'logo.max' => 'Keep the logo under 1MB - large images get emails filtered.',
        ]);

        // Update whichever row exists rather than assuming id 1.
        $setting = EmailSetting::first() ?: new EmailSetting();

        $setting->fill([
            'daily_limit'  => $request->daily_limit,
            'sender_name'  => trim($request->sender_name),
            'sender_email' => strtolower(trim($request->sender_email)),
            'status'       => $request->has('status') ? 1 : 0,
        ]);

        if ($request->hasFile('logo')) {
            $directory = public_path('uploads/branding');

            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            // Replace the previous upload rather than leaving it orphaned.
            if ($setting->logo_path && file_exists(public_path($setting->logo_path))) {
                @unlink(public_path($setting->logo_path));
            }

            $name = 'logo-' . time() . '.' . $request->logo->extension();
            $request->logo->move($directory, $name);
            $setting->logo_path = 'uploads/branding/' . $name;
        }

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            if (file_exists(public_path($setting->logo_path))) {
                @unlink(public_path($setting->logo_path));
            }
            $setting->logo_path = null;
        }

        $setting->save();

        return redirect()->route('email-settings.index')
            ->with('success', 'Email settings updated successfully.');
    }
}
