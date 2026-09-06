@extends('layouts.scaffold')

@section('content')
<div class="container py-4">
    <h4><i class="fa-solid fa-bullhorn me-2"></i> Broadcast</h4>
    <p class="text-muted">
        Send a one-off message to your staff now. This is not scheduled and is not tied to any event.
    </p>

    @include('partials.mail_disabled')

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <form action="{{ route('broadcast.send') }}" method="POST" id="broadcastForm">
                @csrf

                <div class="row">
                    <!-- Audience -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Send to *</label>
                        <select name="audience" id="audience" class="form-select" required>
                            <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>
                                All active employees ({{ $activeCount }})
                            </option>
                            <option value="department" {{ old('audience') === 'department' ? 'selected' : '' }}>
                                A single department
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" id="departmentWrap" style="display:none">
                        <label class="form-label">Department *</label>
                        <select name="department" class="form-select">
                            @foreach($departments as $department)
                                <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                                    {{ $department }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Channel -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Channel *</label>
                        <select name="channel" class="form-select" required>
                            <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>SMS only</option>
                            <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>Email only</option>
                            <option value="both" {{ old('channel') === 'both' ? 'selected' : '' }}>SMS and Email</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Subject</label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}"
                               placeholder="Only used when sending email">
                    </div>

                    <!-- Message -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" id="broadcastMessage" class="form-control" rows="4" required
                                  maxlength="1000">{{ old('message') }}</textarea>
                        <small class="text-muted">
                            <span id="charCount">0</span> characters.
                            Merge tags work here too: <code>@{{ employee_name }}</code>,
                            <code>@{{ department }}</code>, <code>@{{ company_name }}</code>.
                            SMS is billed per 160 characters after tags are filled in.
                        </small>
                        @error('message')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                    </div>
                </div>

                @if($templates->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted">Start from a General template</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($templates as $template)
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="document.getElementById('broadcastMessage').value = this.dataset.body; updateCount();"
                                        data-body="{{ $template->message }}">
                                    {{ $template->template_name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="alert alert-warning small">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirm" required>
                        <label class="form-check-label" for="confirm">
                            I understand this sends immediately to every matching employee and cannot be undone.
                            SMS is charged per message.
                        </label>
                    </div>
                    @error('confirm')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-danger">
                        <i class="fa-solid fa-paper-plane"></i> Send Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const audience = document.getElementById('audience');
    const wrap = document.getElementById('departmentWrap');
    const box = document.getElementById('broadcastMessage');
    const count = document.getElementById('charCount');

    function toggleDepartment() {
        wrap.style.display = audience.value === 'department' ? '' : 'none';
    }
    function updateCount() {
        count.textContent = box.value.length;
    }

    audience.addEventListener('change', toggleDepartment);
    box.addEventListener('input', updateCount);
    toggleDepartment();
    updateCount();

    document.getElementById('broadcastForm').addEventListener('submit', function (e) {
        if (!confirm('Send this message now? It cannot be recalled.')) {
            e.preventDefault();
        }
    });
</script>
@endpush
