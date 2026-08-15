@extends('layouts.scaffold')

@section('content')
<div class="container py-4">

    <h4><i class="fa-solid fa-clock me-2"></i> Automation Schedule</h4>
    <p class="text-muted">Choose the time of day the birthday greetings are sent. All times are in
        <strong>{{ $timezone }}</strong> (Nigeria).</p>

    @if(session('output'))
        <pre class="bg-body-secondary border rounded p-3 small">{{ session('output') }}</pre>
    @endif

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <form action="{{ route('cron-settings.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Send Time -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Send Time *</label>
                        <input type="time" name="run_time" class="form-control"
                               value="{{ old('run_time', substr($setting->run_time ?? '09:00:00', 0, 5)) }}" required>
                        <small class="text-muted">Birthday SMS goes out at this time every day.</small>
                        @error('run_time')
                            <div><small class="text-danger">{{ $message }}</small></div>
                        @enderror
                    </div>

                    <!-- Summary Report Frequency -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Summary Report Frequency</label>
                        <select name="frequency" class="form-select">
                            <option value="daily" {{ old('frequency', $setting->frequency) === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('frequency', $setting->frequency) === 'weekly' ? 'selected' : '' }}>Weekly (Mondays)</option>
                        </select>
                        <small class="text-muted">Applies to the usage report only. Birthday greetings are always checked daily.</small>
                    </div>
                </div>

                <!-- Enable/Disable -->
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="enableCronSwitch"
                           {{ old('status', $setting->status) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enableCronSwitch">Enable automatic sending</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i> Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Next scheduled run</h6>
                    <p class="mb-0 fs-5">
                        @if($nextRun)
                            {{ $nextRun->format('D, d M Y H:i') }}
                        @else
                            <span class="text-danger">Automatic sending is disabled</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Last run</h6>
                    <p class="mb-2 fs-5">
                        {{ $setting->last_run ? \Illuminate\Support\Carbon::parse($setting->last_run)->format('D, d M Y H:i') : 'Never' }}
                    </p>
                    <form action="{{ route('cron-settings.run-now') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-play"></i> Run Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-2 small mb-0">
        <strong>Server setup required:</strong> this page controls <em>when</em> the job runs, but the operating
        system still has to call Laravel every minute. Add this one cron entry on the server:
        <code class="d-block mt-2 text-break">* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
    </div>
</div>
@endsection
