@extends('layouts.scaffold')

@section('content')
<div class="container py-4">

    <h4><i class="fa-solid fa-envelope-circle-check me-2"></i> Email Settings</h4>

    @include('partials.mail_disabled')

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <form action="{{ route('email-settings.store') }}" method="POST">
                @csrf

                <!-- Daily Limit -->
                <div class="mb-3">
                    <label class="form-label">Daily Email Limit *</label>
                    <input type="number" name="daily_limit" class="form-control" min="1"
                           placeholder="Enter limit e.g. 200"
                           value="{{ old('daily_limit', $setting->daily_limit ?? 200) }}" required>
                    @error('daily_limit')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Sender Name -->
                <div class="mb-3">
                    <label class="form-label">Sender Name *</label>
                    <input type="text" name="sender_name" class="form-control"
                           placeholder="Company HR Dept"
                           value="{{ old('sender_name', $setting->sender_name ?? '') }}" required>
                    @error('sender_name')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Sender Email -->
                <div class="mb-3">
                    <label class="form-label">Sender Email *</label>
                    <input type="email" name="sender_email" class="form-control"
                           placeholder="hr@example.com"
                           value="{{ old('sender_email', $setting->sender_email ?? '') }}" required>
                    @error('sender_email')
                        <div><small class="text-danger">{{ $message }}</small></div>
                    @enderror
                </div>

                <!-- Enable/Disable Email Sending -->
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="enableEmailSwitch"
                           {{ old('status', $setting->status ?? 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enableEmailSwitch">Enable Email Notifications</label>
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
