@extends('layouts.scaffold')

@section('content')
<div class="container-fluid px-4 py-5">
    <!-- Header with Welcome Message -->
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Welcome back, {{ Auth::user()->name }}! 👋</h2>
            <p class="text-muted mb-0">Here's what is happening with your employee birthdays today.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.summary') }}" class="btn btn-primary shadow-sm px-4">
                <i class="bi bi-bar-chart-fill me-2"></i>View Reports
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-outline-dark shadow-sm px-4">
                <i class="bi bi-plus-lg me-2"></i>Add Employee
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box bg-primary-light text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <span class="badge bg-success-light text-success">+{{ $activeEmployees }} Active</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalEmployees }}</h3>
                    <p class="text-muted small mb-0">Total Employees</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box bg-info-light text-info">
                            <i class="bi bi-gift-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $upcomingBirthdaysCount }}</h3>
                    <p class="text-muted small mb-0">Birthdays This Month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box bg-warning-light text-warning">
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>
                        <span class="text-muted extra-small">Sent Today</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $emailsSent }}</h3>
                    <p class="text-muted small mb-0">Emails Dispatched</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-box bg-danger-light text-danger">
                            <i class="bi bi-chat-heart-fill"></i>
                        </div>
                        <span class="text-muted extra-small">Sent Today</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $smsSent }}</h3>
                    <p class="text-muted small mb-0">SMS Wishes Sent</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Today's Birthdays Section -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-body border-0 py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Today's Celebrations 🎂</h5>
                        <span class="text-muted small">{{ now()->format('D, d M Y') }}</span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @forelse ($todaysBirthdays as $emp)
                            <div class="col-md-6 col-xl-4">
                                <div class="celebration-card p-3 rounded-4 border">
                                    <div class="d-flex align-items-center mb-3 text-truncate">
                                        <div class="avatar-lg me-3">
                                            <x-avatar :src="$emp->profile_image" :name="$emp->full_name" :size="55"
                                                      class="rounded-circle shadow-sm" />
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-0 text-truncate">{{ $emp->full_name }}</h6>
                                            <p class="text-muted extra-small mb-0 text-truncate">{{ $emp->designation }}</p>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button class="btn btn-sm btn-primary rounded-pill">
                                            <i class="bi bi-send-fill me-1"></i> Send Wish
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="mb-3 text-muted opacity-25">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                </div>
                                <p class="text-muted">No birthdays recorded for today.</p>
                                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-primary">Check Management</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Birthdays List -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-body border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0">Next 7 Days 🎁</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($next7DaysBirthdays as $emp)
                            <div class="list-group-item px-4 py-3 border-0 border-bottom-faint">
                                <div class="d-flex align-items-top justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <x-avatar :src="$emp->profile_image" :name="$emp->full_name" :size="40"
                                                  class="rounded-circle me-3" />
                                        <div class="overflow-hidden">
                                            <h6 class="fw-semibold mb-0 small text-truncate" style="max-width: 140px;">{{ $emp->full_name }}</h6>
                                            <span class="text-muted extra-small">{{ $emp->department }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <div class="fw-bold text-primary small">{{ \Carbon\Carbon::parse($emp->birthday)->format('d M') }}</div>
                                        <span class="text-muted extra-small">In {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($emp->birthday)->year(now()->year)) }} days</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No upcoming birthdays soon.</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-body border-0 py-3 text-center">
                    <a href="{{ route('employees.upcoming-birthdays') }}" class="text-primary text-decoration-none small fw-bold">View Invitation List <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Analytics Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0">Birthday Analytics</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 280px;">
                        <canvas id="birthdayChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-body border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0">System activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="timeline p-4">
                        @forelse($recentLogs as $log)
                            <div class="timeline-item pb-4">
                                <div class="timeline-point {{ $log->status == 'success' ? 'bg-success' : 'bg-danger' }}"></div>
                                <div class="timeline-content ps-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold small text-body">{{ ucfirst($log->type) }} Sent</span>
                                        <span class="text-muted extra-small">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-muted extra-small mb-0">To: {{ Str::limit($log->recipient, 25) }}</p>
                                    @if($log->status != 'success')
                                        <span class="text-danger extra-small d-block">Failed: {{ Str::limit($log->message, 40) }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted small py-4">No recent activity.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('birthdayChart').getContext('2d');
    const birthdayChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Birthdays',
                data: @json($monthlyBirthdays),
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                hoverBackgroundColor: 'rgba(102, 126, 234, 1)',
                borderRadius: 8,
                barThickness: 20,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                    ticks: { precision: 0, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });
</script>
@endpush
@endsection
