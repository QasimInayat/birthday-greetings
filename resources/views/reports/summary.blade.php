@extends('layouts.scaffold')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i> Birthday Summary Report</h3>
        <form action="{{ route('reports.send-email') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" @disabled(!config('mail.enabled'))>
                <i class="fa-solid fa-paper-plane me-2"></i> Send Email Report Now
            </button>
        </form>
    </div>

    @include('partials.mail_disabled')

    <div class="row g-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Today's Birthdays</h6>
                    <h2 class="mb-0">{{ count($todaysBirthdays) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #764ba2 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Upcoming (7 days)</h6>
                    <h2 class="mb-0">{{ count($upcomingBirthdays) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f6ad55 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Emails (30 days)</h6>
                    <h2 class="mb-0">{{ $emailLogs }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #48bb78 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">SMS (30 days)</h6>
                    <h2 class="mb-0">{{ $smsLogs }}</h2>
                </div>
            </div>
        </div>

        <!-- Today's Birthdays Table -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3">
                    <h5 class="mb-0 font-weight-bold text-body">Today's Celebrations 🎂</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todaysBirthdays as $emp)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <x-avatar :src="$emp->profile_image" :name="$emp->full_name" :size="40"
                                                          class="rounded-circle me-3" />
                                                <div>
                                                    <div class="font-weight-bold">{{ $emp->full_name }}</div>
                                                    <div class="small text-muted">{{ $emp->designation }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $emp->department }}</td>
                                        <td><span class="badge bg-success">Today</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No birthdays today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Birthdays List -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3">
                    <h5 class="mb-0 font-weight-bold text-body">Next 7 Days 🎁</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($upcomingBirthdays as $emp)
                            <li class="list-group-item py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <x-avatar :src="$emp->profile_image" :name="$emp->full_name" :size="40"
                                              class="rounded-circle me-3" />
                                    <div>
                                        <div class="font-weight-bold">{{ $emp->full_name }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($emp->birthday)->format('d M') }} (In {{ $emp->days_until }} days)</div>
                                    </div>
                                </div>
                                <span class="badge bg-body-secondary text-body border">Upcoming</span>
                            </li>
                        @empty
                            <div class="text-center py-4 text-muted">No upcoming birthdays soon.</div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- This Month Birthdays -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3">
                    <h5 class="mb-0 font-weight-bold text-body">Full Month Overview ({{ now()->format('F') }})</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($thisMonthBirthdays as $emp)
                            <div class="col-md-2 col-6 text-center mb-3">
                                <x-avatar :src="$emp->profile_image" :name="$emp->full_name" :size="60"
                                          class="rounded-circle mb-2 border p-1" />
                                <div class="small font-weight-bold text-truncate">{{ $emp->full_name }}</div>
                                <div class="small text-primary font-weight-bold">{{ \Carbon\Carbon::parse($emp->birthday)->format('d M') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .font-weight-bold { font-weight: 600 !important; }
    .card { border-radius: 12px; }
    .table th { font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    .badge { padding: 6px 12px; border-radius: 6px; font-weight: 500; }
</style>
@endsection
