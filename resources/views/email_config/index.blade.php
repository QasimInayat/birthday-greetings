@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-envelope-circle-check me-2"></i> Email Configuration</h3>

    @include('partials.mail_disabled')

    <form action="{{ route('email-config.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">

            <!-- SMTP Host -->
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Host *</label>
                <input type="text" name="smtp_host" class="form-control" value="{{ old('smtp_host', $config->smtp_host ?? '') }}" required>
                @error('smtp_host')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- SMTP Port -->
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Port *</label>
                <input type="number" name="smtp_port" class="form-control" value="{{ old('smtp_port', $config->smtp_port ?? '') }}" required>
                @error('smtp_port')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- SMTP Username -->
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Username *</label>
                <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', $config->smtp_username ?? '') }}" required>
                @error('smtp_username')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- SMTP Password -->
            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Password *</label>
                <div class="input-group">
                    <input type="password" id="smtpPassword" name="smtp_password" class="form-control"
                        autocomplete="new-password"
                        placeholder="{{ ($config->smtp_password ?? false) ? 'Saved - leave blank to keep current password' : '' }}"
                        {{ ($config->smtp_password ?? false) ? '' : 'required' }}>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('smtp_password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Encryption -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Encryption Type *</label>
                <select name="encryption" class="form-select" required>
                    <option value="tls" {{ (isset($config) && $config->encryption == 'tls') ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ (isset($config) && $config->encryption == 'ssl') ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ (isset($config) && $config->encryption == 'none') ? 'selected' : '' }}>None</option>
                </select>
                @error('encryption')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-info" onclick="testSMTP()" @disabled(!config('mail.enabled'))>
                <i class="fa-solid fa-plug-circle-check"></i> Test SMTP
            </button>
            <button class="btn btn-success">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // 🔐 Show/Hide password toggle
    function togglePassword() {
        let input = document.getElementById('smtpPassword');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // ✅ AJAX SMTP Test
    function testSMTP() {
        var conf = confirm('Are you sure you want to test the SMTP settings? A test email will be sent to the configured "From" address.');
        if (!conf) return;  
        fetch("{{ route('email-config.test') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message); // Replace alert with toast later
        });
    }
</script>
@endpush
