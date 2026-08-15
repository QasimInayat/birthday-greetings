<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

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
}
