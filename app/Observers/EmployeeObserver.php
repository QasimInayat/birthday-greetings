<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EventSetting;
use App\Services\EventNotifier;

/**
 * Welcome and farewell are not scheduled - they fire when the record changes.
 */
class EmployeeObserver
{
    /**
     * Set true around bulk operations so an import of 100 staff does not
     * fire 100 welcome messages.
     */
    public static bool $muted = false;

    public function created(Employee $employee): void
    {
        if (self::$muted || $employee->status !== 'active') {
            return;
        }

        $this->fire($employee, 'welcome');
    }

    public function updated(Employee $employee): void
    {
        if (self::$muted || !$employee->wasChanged('status')) {
            return;
        }

        if ($employee->status === 'inactive' && $employee->getOriginal('status') === 'active') {
            $this->fire($employee, 'farewell');
        }
    }

    private function fire(Employee $employee, string $eventType): void
    {
        try {
            if (!EventSetting::forType($eventType)->isEnabled()) {
                return;
            }

            (new EventNotifier())->notify($employee, $eventType);
        } catch (\Throwable $e) {
            // Never let a greeting failure break saving an employee record.
            \Illuminate\Support\Facades\Log::error('Employee event failed', [
                'employee' => $employee->full_name,
                'event'    => $eventType,
                'reason'   => $e->getMessage(),
            ]);
        }
    }

    /** Run a callback with welcome/farewell suppressed. */
    public static function muted(callable $callback)
    {
        self::$muted = true;

        try {
            return $callback();
        } finally {
            self::$muted = false;
        }
    }
}
