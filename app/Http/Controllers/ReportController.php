<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // 1. Today's Birthdays
        $todaysBirthdays = Employee::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        // 2. Upcoming Birthdays (Next 7 Days)
        $upcomingBirthdays = collect();
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            $birthdays = Employee::whereMonth('birthday', $date->month)
                ->whereDay('birthday', $date->day)
                ->get();
            if ($birthdays->isNotEmpty()) {
                foreach($birthdays as $b) {
                    $b->days_until = $i;
                    $upcomingBirthdays->push($b);
                }
            }
        }

        // 3. This Month's Birthdays
        $thisMonthBirthdays = Employee::whereMonth('birthday', $today->month)
            ->orderByRaw('DAY(birthday)')
            ->get();

        // 4. Communication Logs Summary (Last 30 Days)
        $emailLogs = Log::where('type', 'email')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
            
        $smsLogs = Log::where('type', 'sms')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('reports.summary', compact(
            'todaysBirthdays',
            'upcomingBirthdays',
            'thisMonthBirthdays',
            'emailLogs',
            'smsLogs'
        ));
    }

    public function sendEmailReport()
    {
        if (!config('mail.enabled')) {
            return redirect()->back()
                ->with('error', 'Outgoing email is currently disabled. Set MAIL_ENABLED=true in the .env file to send reports.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('report:birthday-summary');
            return redirect()->back()->with('success', 'Email summary report has been sent successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send report: ' . $e->getMessage());
        }
    }
}
