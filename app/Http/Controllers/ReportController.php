<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Selectable reporting windows, in days. */
    private const RANGES = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'];

    public function index(Request $request)
    {
        $days = (int) $request->input('days', 30);

        if (!array_key_exists($days, self::RANGES)) {
            $days = 30;
        }

        $today = Carbon::today();
        $from  = $today->copy()->subDays($days - 1);

        // ---- Delivery metrics, split by channel ----
        $counts = Log::selectRaw('type, status, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('type', 'status')
            ->get();

        $metrics = [];
        foreach (['sms', 'email'] as $channel) {
            $sent   = (int) $counts->where('type', $channel)->where('status', 'sent')->sum('total');
            $failed = (int) $counts->where('type', $channel)->where('status', 'failed')->sum('total');
            $all    = (int) $counts->where('type', $channel)->sum('total');

            $metrics[$channel] = [
                'sent'    => $sent,
                'failed'  => $failed,
                'total'   => $all,
                'rate'    => $all > 0 ? round($sent / $all * 100, 1) : null,
            ];
        }

        // ---- Daily delivery trend ----
        $daily = Log::selectRaw('DATE(created_at) as day, type, COUNT(*) as total')
            ->where('created_at', '>=', $from)
            ->where('status', 'sent')
            ->groupBy('day', 'type')
            ->get();

        $labels = [];
        $smsSeries = [];
        $emailSeries = [];

        for ($date = $from->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->toDateString();
            $labels[]      = $date->format('d M');
            $smsSeries[]   = (int) optional($daily->firstWhere(fn ($r) => $r->day == $key && $r->type === 'sms'))->total;
            $emailSeries[] = (int) optional($daily->firstWhere(fn ($r) => $r->day == $key && $r->type === 'email'))->total;
        }

        // ---- Failures worth acting on ----
        $recentFailures = Log::where('status', 'failed')
            ->where('created_at', '>=', $from)
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // ---- Coverage: who can actually be reached ----
        $activeEmployees = Employee::where('status', 'active')->count();
        $missingPhone = Employee::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('phone')->orWhere('phone', ''))
            ->count();
        $missingEmail = Employee::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('email')->orWhere('email', ''))
            ->count();

        // ---- Birthday overview (unchanged behaviour) ----
        $todaysBirthdays = Employee::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        $upcomingBirthdays = collect();
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            foreach (Employee::whereMonth('birthday', $date->month)->whereDay('birthday', $date->day)->get() as $b) {
                $b->days_until = $i;
                $upcomingBirthdays->push($b);
            }
        }

        $thisMonthBirthdays = Employee::whereMonth('birthday', $today->month)
            ->orderByRaw('DAY(birthday)')
            ->get();

        return view('reports.summary', [
            'ranges'          => self::RANGES,
            'days'            => $days,
            'from'            => $from,
            'metrics'         => $metrics,
            'labels'          => $labels,
            'smsSeries'       => $smsSeries,
            'emailSeries'     => $emailSeries,
            'recentFailures'  => $recentFailures,
            'activeEmployees' => $activeEmployees,
            'missingPhone'    => $missingPhone,
            'missingEmail'    => $missingEmail,
            'todaysBirthdays' => $todaysBirthdays,
            'upcomingBirthdays' => $upcomingBirthdays,
            'thisMonthBirthdays' => $thisMonthBirthdays,
            // Kept for the existing stat tiles
            'emailLogs' => $metrics['email']['total'],
            'smsLogs'   => $metrics['sms']['total'],
        ]);
    }

    /**
     * Download the delivery history for the selected window as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $days = (int) $request->input('days', 30);

        if (!array_key_exists($days, self::RANGES)) {
            $days = 30;
        }

        $from = Carbon::today()->subDays($days - 1);
        $filename = 'delivery-report-' . $from->toDateString() . '-to-' . Carbon::today()->toDateString() . '.csv';

        return response()->streamDownload(function () use ($from) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Sent At', 'Type', 'Recipient', 'Subject', 'Status', 'Message']);

            // Chunked so a long history cannot exhaust memory.
            Log::where('created_at', '>=', $from)
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            optional($row->created_at)->format('Y-m-d H:i:s'),
                            $row->type,
                            $row->recipient,
                            $row->subject,
                            $row->status,
                            $row->message,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
