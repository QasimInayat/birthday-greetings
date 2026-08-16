<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogController extends Controller
{
    // Show Logs Page
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('type'); // email or sms

        $logs = Log::when($filter, function ($query, $filter) {
                $query->where('type', $filter);
            })
            ->when($search, function ($query, $search) {
                // Grouped, otherwise the orWhere would cancel out the type filter.
                $query->where(function ($q) use ($search) {
                    $q->where('recipient', 'like', "%$search%")
                      ->orWhere('subject', 'like', "%$search%")
                      ->orWhere('message', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('logs.index', compact('logs', 'filter', 'search'));
    }

    /**
     * Delete delivery history, either everything or entries past a given age.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'range' => 'required|in:all,30,90',
        ]);

        $query = Log::query();

        if ($request->range !== 'all') {
            $cutoff = Carbon::today()->subDays((int) $request->range);
            $query->where('created_at', '<', $cutoff);
        }

        $deleted = $query->delete();

        if ($deleted === 0) {
            return redirect()->route('logs.index')
                ->with('success', 'No log entries matched — nothing was deleted.');
        }

        $message = $deleted . ' log ' . Str::plural('entry', $deleted) . ' deleted.';

        // Today's entries are what stop a re-run greeting the same person twice.
        if ($request->range === 'all') {
            $message .= ' Note: today\'s entries are gone, so running the birthday job again today would send duplicate messages.';
        }

        return redirect()->route('logs.index')->with('success', $message);
    }
}
