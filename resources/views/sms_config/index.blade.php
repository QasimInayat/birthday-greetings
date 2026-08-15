@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-mobile-screen-button me-2"></i> SMS Configuration
        <small class="text-muted fs-6">(BestBulkSMS)</small>
    </h3>

    <form action="{{ route('sms-config.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">

            <!-- SMS API URL -->
            <div class="col-md-6 mb-3">
                <label class="form-label">SMS API URL *</label>
                <input type="text" name="api_url" class="form-control"
                       placeholder="https://www.bestbulksms.com.ng/api/sms/send"
                       value="{{ old('api_url', $config->api_url ?? config('services.bestbulksms.send_url')) }}" required>
                <small class="text-muted">BestBulkSMS send endpoint.</small>
                @error('api_url')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- API Key -->
            <div class="col-md-6 mb-3">
                <label class="form-label">API Key / Token {{ ($config->api_key ?? false) ? '' : '*' }}</label>
                <input type="password" name="api_key" class="form-control" autocomplete="new-password"
                       placeholder="{{ ($config->api_key ?? false) ? 'Saved - leave blank to keep current key' : 'Paste your API key' }}"
                       {{ ($config->api_key ?? false) ? '' : 'required' }}>
                <small class="text-muted">
                    @if($config->api_key ?? false)
                        Current key ends in <code>{{ substr($config->api_key, -4) }}</code>. For security the full key is never shown.
                    @else
                        The key is stored in the database and never displayed again.
                    @endif
                </small>
                @error('api_key')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Sender ID -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Sender ID *</label>
                <input type="text" name="sender_id" class="form-control" maxlength="20"
                       placeholder="BESTBULKSMS"
                       value="{{ old('sender_id', $config->sender_id ?? config('services.bestbulksms.sender_id')) }}" required>
                @error('sender_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Route -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Route</label>
                <input type="text" class="form-control" value="{{ config('services.bestbulksms.route') }}" disabled>
                <small class="text-muted">Set with <code>BESTBULKSMS_ROUTE</code> in the .env file.</small>
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testSmsModal">
                <i class="fa-solid fa-paper-plane"></i> Test SMS
            </button>
            <button class="btn btn-success">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </div>
    </form>

    <div class="row mt-4">
        <!-- Wallet Balance -->
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-wallet me-2"></i> Wallet Balance</h5>
                    <p class="mb-2 text-muted" id="balanceResult">Not checked yet.</p>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="checkBalance()">
                        <i class="fa-solid fa-rotate"></i> Check Balance
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Status -->
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-circle-info me-2"></i> Message Status</h5>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" id="smsMessageId" class="form-control" placeholder="SMS Message ID e.g. 999">
                        <button type="button" class="btn btn-outline-primary" onclick="checkMessageStatus()">
                            <i class="fa-solid fa-magnifying-glass"></i> Check
                        </button>
                    </div>
                    <p class="mb-0 text-muted" id="statusResult">Enter a message ID returned by the send API.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test SMS Modal -->
<div class="modal fade" id="testSmsModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="testSmsForm" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Send Test SMS</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <label>Enter Phone Number</label>
              <input type="text" id="testPhone" class="form-control" placeholder="e.g. 2348012345678 or 08012345678" required>
              <small class="text-muted">Local numbers starting with 0 are converted to international format automatically.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="sendTestSms()">Send Test SMS</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function sendTestSms() {
    const phone = document.getElementById('testPhone').value;

    if (!phone) {
        alert('Please enter a phone number.');
        return;
    }

    fetch("{{ route('sms-config.test') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => res.json())
    .then(data => alert(data.message || 'Test SMS failed.'))
    .catch(err => alert('Test SMS failed.'));
}

function checkBalance() {
    const target = document.getElementById('balanceResult');
    target.textContent = 'Checking...';

    fetch("{{ route('sms-config.balance') }}", {
        headers: { "Accept": "application/json" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const wallet = data.data || {};
            const amount = wallet.balance ?? wallet.ledger_balance ?? wallet.wallet_balance;
            target.textContent = (amount !== undefined && amount !== null)
                ? 'Available balance: ' + amount
                : JSON.stringify(wallet);
        } else {
            target.textContent = data.message || 'Could not fetch balance.';
        }
    })
    .catch(err => { target.textContent = 'Could not fetch balance.'; });
}

function checkMessageStatus() {
    const id = document.getElementById('smsMessageId').value;
    const target = document.getElementById('statusResult');

    if (!id) {
        alert('Please enter an SMS message ID.');
        return;
    }

    target.textContent = 'Checking...';

    fetch("{{ route('sms-config.message-status') }}?sms_message_id=" + encodeURIComponent(id), {
        headers: { "Accept": "application/json" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const d = data.data || {};
            target.textContent = 'Status: ' + (d.message_status ?? 'unknown')
                + ' | recipients: ' + (d.recipients ?? '-')
                + ' | units: ' + (d.units ?? '-')
                + ' | cost: ' + (d.total_cost ?? '-')
                + ' | sent: ' + (d.created_at ?? '-');
        } else {
            target.textContent = data.message || 'Could not fetch message status.';
        }
    })
    .catch(err => { target.textContent = 'Could not fetch message status.'; });
}
</script>
@endpush
