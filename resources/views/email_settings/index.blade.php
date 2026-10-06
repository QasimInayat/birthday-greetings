@extends('layouts.scaffold')

@section('content')
<div class="container py-4">

    <h4><i class="fa-solid fa-envelope-circle-check me-2"></i> Email Settings</h4>

    @include('partials.mail_disabled')

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <form action="{{ route('email-settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Email Logo -->
                <div class="mb-4">
                    <label class="form-label">Email Logo</label>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="border rounded p-2 text-center" style="min-width:140px">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Current logo" style="max-height:60px; max-width:180px">
                            @else
                                <span class="text-muted small">No logo</span>
                            @endif
                        </div>
                        <div class="flex-grow-1" style="min-width:16rem">
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/gif">
                            <small class="text-muted">
                                PNG or JPG, under 1MB. Shown at the top of every email that uses the standard layout.
                                @if($usingDefaultLogo && $logoUrl)
                                    Currently showing the application logo — upload one to replace it.
                                @endif
                            </small>
                            @error('logo')<div><small class="text-danger">{{ $message }}</small></div>@enderror

                            @if(!$usingDefaultLogo)
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                    <label class="form-check-label small" for="removeLogo">Remove the uploaded logo</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(!\Illuminate\Support\Str::startsWith(config('app.url'), ['https://', 'http://']) || config('app.url') === 'http://localhost')
                        <div class="alert alert-warning small mt-2 mb-0">
                            <strong>APP_URL is <code>{{ config('app.url') }}</code>.</strong>
                            Email clients cannot load images from that address — set APP_URL to your real
                            domain in <code>.env</code> or the logo will appear broken in inboxes.
                        </div>
                    @endif
                </div>

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
