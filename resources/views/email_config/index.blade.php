@extends('layouts.scaffold')

@section('content')
<div class="container py-4">
    <h3 class="mb-1"><i class="fa-solid fa-envelope-circle-check me-2"></i> SMTP Server</h3>
    <p class="text-muted">
        These settings are read from the <code>.env</code> file on the server. They cannot be edited here —
        that keeps your SMTP password out of the database and out of this page's HTML.
    </p>

    @include('partials.mail_disabled')

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        <tr>
                            <th style="width:220px">Outgoing email</th>
                            <td>
                                @if($mailEnabled)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                                <code class="ms-2">MAIL_ENABLED</code>
                            </td>
                        </tr>
                        <tr>
                            <th>SMTP Host</th>
                            <td>{{ $host ?: '—' }} <code class="ms-2">MAIL_HOST</code></td>
                        </tr>
                        <tr>
                            <th>SMTP Port</th>
                            <td>{{ $port ?: '—' }} <code class="ms-2">MAIL_PORT</code></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ $username ?: '—' }} <code class="ms-2">MAIL_USERNAME</code></td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td>
                                @if($hasPassword)
                                    <span class="badge bg-success">Set</span>
                                @else
                                    <span class="badge bg-danger">Not set</span>
                                @endif
                                <code class="ms-2">MAIL_PASSWORD</code>
                            </td>
                        </tr>
                        <tr>
                            <th>Encryption</th>
                            <td>{{ $encryption ?: '—' }} <code class="ms-2">MAIL_ENCRYPTION</code></td>
                        </tr>
                        <tr>
                            <th>Sending as</th>
                            <td>{{ $fromName }} &lt;{{ $fromAddress ?: '—' }}&gt;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h6 class="card-title mb-2">Send a test email</h6>
            <p class="text-muted small">
                Sends one email using these settings, through the standard layout — so it also shows
                you how the logo and branding look in a real inbox.
            </p>
            <div class="input-group" style="max-width:34rem">
                <input type="email" id="testTo" class="form-control"
                       placeholder="your-name@example.com"
                       value="{{ $fromAddress }}">
                <button type="button" class="btn btn-info" onclick="testSMTP()" @disabled(!$mailEnabled)>
                    <i class="fa-solid fa-paper-plane"></i> Send Test
                </button>
            </div>
            <small class="text-muted">Use an inbox you can actually open — not a noreply address.</small>
        </div>
    </div>

    <div class="alert alert-info small mt-3 mb-0">
        <strong>To change these:</strong> edit <code>.env</code> on the server, then run
        <code class="d-block mt-2 text-break">php artisan config:cache</code>
        The change has no effect until that command runs.
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testSMTP() {
        const to = document.getElementById('testTo').value.trim();

        if (!to) {
            alert('Enter the address to send the test to.');
            return;
        }

        if (!confirm('Send a test email to ' + to + '?')) return;

        fetch("{{ route('email-config.test') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ to: to })
        })
        .then(res => res.json())
        .then(data => alert(data.message || 'Test failed.'))
        .catch(() => alert('Test failed.'));
    }
</script>
@endpush
