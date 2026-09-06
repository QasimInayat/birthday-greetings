@extends('layouts.scaffold')

@section('content')
<div class="container py-4">
    <h4><i class="fa-solid fa-bell me-2"></i> Employee Events</h4>
    <p class="text-muted">
        Each event sends the template marked <strong>Default</strong> for its type.
        Turn an event on here and it runs by itself.
    </p>

    @foreach($rows as $row)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form action="{{ route('event-settings.update', $row['key']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1">
                                {{ $row['label'] }}
                                @if($row['setting']->status)
                                    <span class="badge bg-success ms-1">On</span>
                                @else
                                    <span class="badge bg-secondary ms-1">Off</span>
                                @endif
                            </h5>
                            <div class="small text-muted">{{ $row['trigger'] }}</div>

                            <div class="small mt-2">
                                SMS template:
                                @if($row['smsTemplate'])
                                    <strong>{{ $row['smsTemplate']->template_name }}</strong>
                                @else
                                    <span class="text-danger">none — create one of type {{ $row['label'] }}</span>
                                @endif
                                &nbsp;·&nbsp; Email template:
                                @if($row['mailTemplate'])
                                    <strong>{{ $row['mailTemplate']->template_name }}</strong>
                                @else
                                    <span class="text-danger">none</span>
                                @endif
                            </div>

                            @if($row['key'] === 'anniversary' && $missingJoinDate > 0)
                                <div class="alert alert-warning py-2 px-3 small mt-2 mb-0">
                                    {{ $missingJoinDate }} of {{ $activeCount }} active employees have no joining date —
                                    they will never receive an anniversary message.
                                    <a href="{{ route('employees.index') }}" class="alert-link">Add their dates</a>.
                                </div>
                            @endif

                            @if($row['key'] === 'general')
                                <div class="small text-muted mt-2">
                                    Send these from <a href="{{ route('broadcast.index') }}">Broadcast</a>.
                                </div>
                            @endif
                        </div>

                        @if($row['automated'])
                            <div class="text-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                           id="status-{{ $row['key'] }}" {{ $row['setting']->status ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status-{{ $row['key'] }}">Enabled</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_sms" value="1"
                                           id="sms-{{ $row['key'] }}" {{ $row['setting']->send_sms ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms-{{ $row['key'] }}">Send SMS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_email" value="1"
                                           id="mail-{{ $row['key'] }}" {{ $row['setting']->send_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mail-{{ $row['key'] }}">Send Email</label>
                                </div>

                                @if($row['key'] === 'anniversary')
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="milestones_only" value="1"
                                               id="ms-{{ $row['key'] }}" {{ $row['setting']->milestones_only ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ms-{{ $row['key'] }}">
                                            Milestones only
                                            <span class="text-muted d-block" style="font-size:11px">
                                                {{ implode(', ', \App\Models\EventSetting::MILESTONES) }} years
                                            </span>
                                        </label>
                                    </div>
                                @endif

                                <button class="btn btn-sm btn-success mt-2">
                                    <i class="fa-solid fa-save"></i> Save
                                </button>
                            </div>
                        @else
                            <div class="text-end text-muted small" style="max-width:14rem">
                                Manual only — nothing to schedule.
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="alert alert-info small mb-0">
        Scheduled events (Birthday, Anniversary) all run at the single time set on
        <a href="{{ route('cron-settings.index') }}" class="alert-link">Automation</a>.
        Welcome and Farewell send immediately when an employee is added or marked inactive.
    </div>
</div>
@endsection
