<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // --- Summary Statistics ---
        $totalEmployees = Employee::count();

        // Birthdays falling in the next 30 days, wrapping across the year end.
        $upcomingBirthdaysCount = Employee::whereRaw(
                'MOD(DAYOFYEAR(birthday) - DAYOFYEAR(?) + 366, 366) BETWEEN 0 AND 30',
                [$today->toDateString()]
            )
            ->count();
        $emailsSent = Log::where('type', 'email')->whereDate('created_at', $today)->count();
        $smsSent = Log::where('type', 'sms')->whereDate('created_at', $today)->count();

        // --- Today's Birthdays ---
        $todaysBirthdays = Employee::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        // --- Next 7 Days Birthdays (for the list) ---
        $next7DaysBirthdays = Employee::where(function($query) use ($today) {
                for($i=1; $i<=7; $i++) {
                    $d = $today->copy()->addDays($i);
                    $query->orWhere(function($q) use ($d) {
                        $q->whereMonth('birthday', $d->month)
                          ->whereDay('birthday', $d->day);
                    });
                }
            })
            ->orderByRaw('MONTH(birthday), DAY(birthday)')
            ->take(5)
            ->get();

        // --- Chart Data: Birthdays per Month ---
        $monthlyBirthdays = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyBirthdays[] = Employee::whereMonth('birthday', $i)->count();
        }

        // --- Chart Data: Department Distribution ---
        $departmentStats = Employee::select('department', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();

        // --- Recent Communication Logs ---
        $recentLogs = Log::orderBy('id', 'desc')->take(6)->get();

        // --- System Health / Usage ---
        // status is an enum('active','inactive'), not a boolean.
        $activeEmployees = Employee::where('status', 'active')->count();

        return view('dashboard.index', compact(
            'totalEmployees',
            'upcomingBirthdaysCount',
            'emailsSent',
            'smsSent',
            'todaysBirthdays',
            'next7DaysBirthdays',
            'monthlyBirthdays',
            'departmentStats',
            'recentLogs',
            'activeEmployees'
        ));
    }


 
}
