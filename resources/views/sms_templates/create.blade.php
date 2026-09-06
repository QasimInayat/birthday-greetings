@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-plus me-2"></i> Add SMS Template</h3>

    <form action="{{ route('sms-templates.store') }}" method="POST">
        @csrf

        <!-- Template Name -->
        <div class="mb-3">
            <label class="form-label">Template Name *</label>
            <input type="text" name="template_name" class="form-control" value="{{ old('template_name') }}" required>
            @error('template_name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Template Type -->
        <div class="mb-3">
            <label class="form-label">Template Type *</label>
            <select name="template_type" class="form-select" required>
                @foreach ($templateTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('template_type', 'birthday') === $key ? 'selected' : '' }}>
                        {{ $label }}{{ in_array($key, $automatedTypes) ? '' : ' (not yet sent automatically)' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Which employee event this message is written for.</small>
            @error('template_type')
                <div><small class="text-danger">{{ $message }}</small></div>
            @enderror
        </div>

        <!-- Default -->
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault"
                   {{ old('is_default') ? 'checked' : '' }}>
            <label class="form-check-label" for="isDefault">
                Use this as the default template for its type
            </label>
            <div><small class="text-muted">The default is the one the system actually sends. Only one per type.</small></div>
        </div>

        <!-- Merge Tags -->
        <div class="mb-2">
            <label class="form-label">Insert Merge Tags:</label><br>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('employee_name')">Employee Name</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('department')">Department</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('birthday_date')">Birthday</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('company_name')">Company Name</button>
        </div>

        <!-- SMS Message -->
        <div class="mb-3">
            <label class="form-label">Message (max 160 characters) *</label>
            <textarea name="message" id="smsMessage" class="form-control" rows="3" maxlength="160" required>{{ old('message') }}</textarea>
            <small id="charCount" class="text-muted">Characters: 0 / 160</small>
            @error('message')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('sms-templates.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Save Template</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // ✅ Insert merge tags safely
    function insertTag(tag) {
        let textarea = document.getElementById('smsMessage');
        textarea.value += ` @{{ ${tag} }} `;
        updateCharCount();
    }

    // ✅ Character counter
    function updateCharCount() {
        let message = document.getElementById('smsMessage').value;
        document.getElementById('charCount').textContent = `Characters: ${message.length} / 160`;
    }

    document.getElementById('smsMessage').addEventListener('input', updateCharCount);
    updateCharCount();
</script>
@endpush
