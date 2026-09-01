@extends('layouts.scaffold')

@section('content')
<div class="container py-4">

    <h4><i class="fa-solid fa-message me-2"></i> SMS Settings</h4>

    {{-- State outright which template the cron will use, so it is never a guess. --}}
    @if(!$active)
        <div class="alert alert-danger">
            <strong>No SMS template exists.</strong>
            Birthday messages will fall back to a generic built-in message.
            <a href="{{ route('sms-templates.create') }}" class="alert-link">Create a template</a> first.
        </div>
    @elseif($isFallback)
        <div class="alert alert-warning">
            <strong>No template is selected.</strong>
            Until you choose one below and save, birthday messages will use
            <strong>"{{ $active->template_name }}"</strong> as a fallback.
        </div>
    @else
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check me-1"></i>
            Birthday messages are being sent using <strong>"{{ $active->template_name }}"</strong>.
            <div class="small mt-2 mb-0 fst-italic">{{ $active->message }}</div>
        </div>
    @endif

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <form action="{{ route('sms-settings.store') }}" method="POST">
                @csrf

                <!-- Birthday SMS Template -->
                <div class="mb-3">
                    <label class="form-label">Birthday SMS Template *</label>
                    <select name="sms_template_id" class="form-select" required>
                        <option value="">-- Select a template --</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}"
                                {{ (int) old('sms_template_id', $setting->sms_template_id ?? 0) === $template->id ? 'selected' : '' }}>
                                {{ $template->template_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">This is the message sent to every employee whose birthday is today.</small>
                    @error('sms_template_id')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Daily SMS Limit -->
                <div class="mb-3">
                    <label class="form-label">Daily SMS Limit *</label>
                    <input type="number" name="daily_limit" class="form-control" min="1"
                           placeholder="Enter limit e.g. 100"
                           value="{{ old('daily_limit', $setting->daily_limit ?? 100) }}" required>
                    <small class="text-muted">Maximum SMS the system may send in one day.</small>
                    @error('daily_limit')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Sender ID -->
                <div class="mb-3">
                    <label class="form-label">Sender ID *</label>
                    <input type="text" name="sender_id" class="form-control" maxlength="20"
                           placeholder="CompanyName"
                           value="{{ old('sender_id', $setting->sender_id ?? '') }}" required>
                    <small class="text-muted">Shown as the sender on the recipient's phone.</small>
                    @error('sender_id')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Enable/Disable SMS -->
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="enableSmsSwitch"
                           {{ old('status', $setting->status ?? 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enableSmsSwitch">Enable SMS Notifications</label>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Settings</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
