<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $timezone  = config('app.timezone');
        $time      = '09:00';
        $frequency = 'daily';
        $enabled   = true;

        try {
            $cronSetting = \App\Models\CronSetting::first();

            if ($cronSetting) {
                $enabled   = (bool) $cronSetting->status;
                $time      = substr((string) $cronSetting->run_time, 0, 5) ?: $time;
                $frequency = $cronSetting->frequency ?: $frequency;
            }
        } catch (\Throwable $e) {
            // schedule() runs for every artisan call - if the database is not
            // reachable yet, fall back to the defaults instead of breaking artisan.
        }

        if (!$enabled) {
            return;
        }

        // Birthdays must be checked every single day: a birthday falling on a
        // day the job does not run would be missed for a whole year.
        $schedule->command('birthday:send-wishes')
            ->dailyAt($time)
            ->timezone($timezone)
            ->withoutOverlapping();

        $summary = $schedule->command('report:birthday-summary')->timezone($timezone);

        $frequency === 'weekly'
            ? $summary->weeklyOn(1, $time) // Mondays
            : $summary->dailyAt($time);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
