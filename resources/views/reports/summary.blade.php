@extends('layouts.scaffold')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i> Usage Report</h3>
        <form action="{{ route('reports.send-email') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" @disabled(!config('mail.enabled'))>
                <i class="fa-solid fa-paper-plane me-2"></i> Send Email Report Now
            </button>
        </form>
    </div>

    @include('partials.mail_disabled')

    {{-- Filters in one row above the charts --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="btn-group" role="group" aria-label="Reporting period">
            @foreach($ranges as $value => $label)
                <a href="{{ route('reports.summary', ['days' => $value]) }}"
                   class="btn btn-sm {{ $days === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">{{ $from->format('d M Y') }} — {{ now()->format('d M Y') }}</span>
            <a href="{{ route('reports.export', ['days' => $days]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Delivery metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--viz-sms) !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">SMS Delivered</h6>
                    <h2 class="mb-0">{{ number_format($metrics['sms']['sent']) }}</h2>
                    <div class="small text-muted">
                        @if($metrics['sms']['failed'] > 0)
                            <span class="text-danger">{{ $metrics['sms']['failed'] }} failed</span> ·
                        @endif
                        {{ $metrics['sms']['rate'] !== null ? $metrics['sms']['rate'] . '% success' : 'no activity' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--viz-email) !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Emails Delivered</h6>
                    <h2 class="mb-0">{{ number_format($metrics['email']['sent']) }}</h2>
                    <div class="small text-muted">
                        @if($metrics['email']['failed'] > 0)
                            <span class="text-danger">{{ $metrics['email']['failed'] }} failed</span> ·
                        @endif
                        {{ $metrics['email']['rate'] !== null ? $metrics['email']['rate'] . '% success' : 'no activity' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Today's Birthdays</h6>
                    <h2 class="mb-0">{{ count($todaysBirthdays) }}</h2>
                    <div class="small text-muted">{{ count($upcomingBirthdays) }} in the next 7 days</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #48bb78 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Reachable Staff</h6>
                    <h2 class="mb-0">{{ number_format($activeEmployees - $missingPhone) }}<span class="fs-6 text-muted">/{{ number_format($activeEmployees) }}</span></h2>
                    <div class="small text-muted">
                        @if($missingPhone > 0)
                            <span class="text-danger">{{ $missingPhone }} without a phone number</span>
                        @else
                            every active employee has a phone number
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery trend -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 font-weight-bold text-body">Messages Delivered per Day</h5>
                    <div class="d-flex gap-3 small">
                        <span><span class="viz-key" style="background: var(--viz-sms)"></span> SMS</span>
                        <span><span class="viz-key" style="background: var(--viz-email)"></span> Email</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(array_sum($smsSeries) + array_sum($emailSeries) === 0)
                        <p class="text-muted text-center py-4 mb-0">
                            Nothing was delivered in this period.
                        </p>
                    @else
                        <div style="height:280px"><canvas id="deliveryTrend"></canvas></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Failures -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3">
                    <h5 class="mb-0 font-weight-bold text-body">
                        Recent Failures
                        @if($recentFailures->isNotEmpty())
                            <span class="badge bg-danger ms-1">{{ $metrics['sms']['failed'] + $metrics['email']['failed'] }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>When</th>
                                    <th>Type</th>
                                    <th>Recipient</th>
                                    <th>Subject</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentFailures as $failure)
                                    <tr>
                                        <td>{{ optional($failure->created_at)->format('d M H:i') }}</td>
                                        <td>{{ strtoupper($failure->type) }}</td>
                                        <td>{{ $failure->recipient }}</td>
                                        <td>{{ $failure->subject ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No failures in this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($recentFailures->isNotEmpty())
                    <div class="card-footer bg-body border-0 text-end">
                        <a href="{{ route('logs.index', ['search' => '']) }}" class="small">View all delivery logs →</a>
                    </div>
                @endif
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

    /* Categorical slots 1 and 2, validated for contrast and colour-vision
       deficiency against both surfaces. Assigned in fixed order: SMS, Email. */
    :root {
        --viz-sms:   #2a78d6;
        --viz-email: #eb6834;
        --viz-grid:  rgba(15, 23, 42, 0.08);
        --viz-ink:   #5c6981;
    }
    [data-bs-theme="dark"] {
        --viz-sms:   #3987e5;
        --viz-email: #d95926;
        --viz-grid:  rgba(255, 255, 255, 0.10);
        --viz-ink:   #8b99ae;
    }

    .viz-key {
        display: inline-block;
        width: 10px; height: 10px;
        border-radius: 2px;
        margin-right: 4px;
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const canvas = document.getElementById('deliveryTrend');
    if (!canvas) return;

    const labels = @json($labels);
    const smsData = @json($smsSeries);
    const emailData = @json($emailSeries);

    const readToken = (name) =>
        getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    let chart;

    function build() {
        if (chart) chart.destroy();

        const ink  = readToken('--viz-ink');
        const grid = readToken('--viz-grid');

        chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'SMS',
                        data: smsData,
                        borderColor: readToken('--viz-sms'),
                        backgroundColor: readToken('--viz-sms'),
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 2
                    },
                    {
                        label: 'Email',
                        data: emailData,
                        borderColor: readToken('--viz-email'),
                        backgroundColor: readToken('--viz-email'),
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // Crosshair behaviour: hovering anywhere on a day shows both series.
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    // Identity is carried by the legend in the card header.
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (c) => ` ${c.dataset.label}: ${c.parsed.y} delivered`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: ink, maxRotation: 0, autoSkipPadding: 16 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: grid, drawBorder: false },
                        ticks: { color: ink, precision: 0 }
                    }
                }
            }
        });
    }

    build();

    // The theme toggle swaps data-bs-theme on <html>; restyle rather than flip.
    new MutationObserver(build).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-bs-theme']
    });
})();
</script>
@endpush
