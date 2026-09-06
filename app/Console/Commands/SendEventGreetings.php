<?php

namespace App\Console\Commands;

use App\Models\CronSetting;
use App\Models\Employee;
use App\Models\EventSetting;
use App\Services\EventNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendEventGreetings extends Command
{
    protected $signature = 'events:send
                            {--event= : Only run this event (birthday, anniversary)}
                            {--dry-run : List who would be messaged without sending}';

    protected $description = 'Send greetings for every date-driven employee event due today';

    /** Events driven by a date on the employee record. */
    private const SCHEDULED = ['birthday', 'anniversary'];

    public function handle()
    {
        $today = Carbon::today();
        $this->info('Checking events for ' . $today->format('d M Y') . ' (' . config('app.timezone') . ')...');

        $only = $this->option('event');
        $dryRun = $this->option('dry-run');
        $notifier = new EventNotifier();

        $this->touchLastRun();

        if (!config('mail.enabled')) {
            $this->warn('Outgoing email is disabled (MAIL_ENABLED=false) - SMS only.');
        }

        $anySent = false;

        foreach (self::SCHEDULED as $eventType) {
            if ($only && $only !== $eventType) {
                continue;
            }

            $setting = EventSetting::forType($eventType);

            if (!$setting->isEnabled()) {
                $this->line(ucfirst($eventType) . ': switched off, skipping.');
                continue;
            }

            $employees = $this->matchesFor($eventType, $today, $setting);

            if ($employees->isEmpty()) {
                $this->line(ucfirst($eventType) . ': nobody today.');
                continue;
            }

            $this->info(ucfirst($eventType) . ': ' . $employees->count() . ' employee(s).');

            foreach ($employees as $employee) {
                if ($dryRun) {
                    $this->line("  would send {$eventType} to {$employee->full_name}");
                    continue;
                }

                $delivered = $notifier->notify($employee, $eventType);

                if ($delivered) {
                    $anySent = true;
                    $this->info("  sent {$eventType} to {$employee->full_name} via " . implode(' + ', $delivered));
                } else {
                    $this->line("  nothing sent to {$employee->full_name}");
                }
            }
        }

        foreach (array_unique($notifier->skipped) as $reason) {
            $this->line('  skipped: ' . $reason);
        }

        if (!$dryRun) {
            $this->info(sprintf('Done. %d SMS, %d email sent.', $notifier->sent['sms'], $notifier->sent['email']));
        }

        return self::SUCCESS;
    }

    /**
     * Employees whose stored date matches today for this event.
     */
    private function matchesFor(string $eventType, Carbon $today, EventSetting $setting)
    {
        $column = $eventType === 'anniversary' ? 'date_of_joining' : 'birthday';

        // In a non-leap year, greet 29 February people on the 28th.
        $includeLeapDay = $today->month === 2 && $today->day === 28 && !$today->isLeapYear();

        $employees = Employee::where('status', 'active')
            ->whereNotNull($column)
            ->where(function ($query) use ($column, $today, $includeLeapDay) {
                $query->where(function ($q) use ($column, $today) {
                    $q->whereMonth($column, $today->month)->whereDay($column, $today->day);
                });

                if ($includeLeapDay) {
                    $query->orWhere(function ($q) use ($column) {
                        $q->whereMonth($column, 2)->whereDay($column, 29);
                    });
                }
            })
            ->get();

        if ($eventType !== 'anniversary') {
            return $employees;
        }

        // Someone who joined today is not celebrating an anniversary yet.
        $employees = $employees->filter(fn ($e) => ($e->yearsOfService() ?? 0) >= 1);

        if ($setting->milestones_only) {
            $employees = $employees->filter(
                fn ($e) => in_array($e->yearsOfService(), EventSetting::MILESTONES, true)
            );
        }

        return $employees->values();
    }

    private function touchLastRun(): void
    {
        try {
            if ($cron = CronSetting::first()) {
                $cron->update(['last_run' => Carbon::now()]);
            }
        } catch (\Throwable $e) {
            $this->warn('Could not update last run time: ' . $e->getMessage());
        }
    }
}
