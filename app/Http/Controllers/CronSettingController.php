<?php

namespace App\Http\Controllers;

use App\Models\CronSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

class CronSettingController extends Controller
{
    // Show Automation / Cron settings page
    public function index()
    {
        $setting = $this->currentSetting();

        return view('cron_settings.index', [
            'setting'  => $setting,
            'timezone' => config('app.timezone'),
            'nextRun'  => $this->nextRunAt($setting),
        ]);
    }

    // Save schedule
    public function store(Request $request)
    {
        $request->validate([
            'run_time'  => 'required|date_format:H:i',
            'frequency' => 'required|in:daily,weekly',
        ]);

        CronSetting::updateOrCreate(
            ['id' => 1], // Always single record
            [
                'run_time'  => $request->run_time,
                'frequency' => $request->frequency,
                'status'    => $request->has('status') ? 1 : 0,
            ]
        );

        return redirect()->route('cron-settings.index')
            ->with('success', 'Automation schedule saved. Birthday wishes will be sent at '
                . $request->run_time . ' ' . config('app.timezone') . ' each day.');
    }

    // Run the birthday job immediately (for testing)
    public function runNow()
    {
        Artisan::call('birthday:send-wishes');

        return redirect()->route('cron-settings.index')
            ->with('success', 'Birthday job executed.')
            ->with('output', Artisan::output());
    }

    private function currentSetting(): CronSetting
    {
        $setting = CronSetting::first();

        if (!$setting) {
            // Sensible default so the page and the scheduler agree before the admin saves.
            $setting = new CronSetting([
                'frequency' => 'daily',
                'run_time'  => '09:00:00',
                'status'    => true,
            ]);
        }

        return $setting;
    }

    private function nextRunAt(CronSetting $setting): ?Carbon
    {
        if (!$setting->status) {
            return null;
        }

        $time = substr((string) $setting->run_time, 0, 5) ?: '09:00';
        [$hour, $minute] = array_pad(explode(':', $time), 2, 0);

        $next = Carbon::now(config('app.timezone'))->setTime((int) $hour, (int) $minute, 0);

        if ($next->isPast()) {
            $next->addDay();
        }

        return $next;
    }
}
