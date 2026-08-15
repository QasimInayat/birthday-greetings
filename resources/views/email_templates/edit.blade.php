@extends('layouts.scaffold')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
@endpush
@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Email Template</h3>

    <!-- ✅ Update Template Form -->
    <form action="{{ route('email-templates.update', $template->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Template Name -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Template Name *</label>
                <input type="text" name="template_name" class="form-control"
                       value="{{ old('template_name', $template->template_name) }}" required>
                @error('template_name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Template Type -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Template Type *</label>
                <select name="template_type" class="form-select" required>
                    <option value="birthday" {{ $template->template_type=='birthday' ? 'selected' : '' }}>Birthday</option>
                    <option value="anniversary" {{ $template->template_type=='anniversary' ? 'selected' : '' }}>Anniversary</option>
                    <option value="welcome" {{ $template->template_type=='welcome' ? 'selected' : '' }}>Welcome</option>
                    <option value="farewell" {{ $template->template_type=='farewell' ? 'selected' : '' }}>Farewell</option>
                    <option value="general" {{ $template->template_type=='general' ? 'selected' : '' }}>General</option>
                </select>
                @error('template_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Subject -->
            <div class="col-md-12 mb-3">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control"
                       value="{{ old('subject', $template->subject) }}" required>
                @error('subject')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Merge Tags -->
            <div class="col-md-12 mb-2">
                <label class="form-label">Insert Merge Tags:</label><br>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('employee_name')">Employee Name</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('department')">Department</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('birthday_date')">Birthday</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTag('company_name')">Company Name</button>
            </div>

            <!-- Content -->
            <div class="col-md-12 mb-3">
                <label class="form-label">Email Content *</label>
                <textarea id="emailEditor" name="content">{{ old('content', $template->content) }}</textarea>
                @error('content')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <!-- ✅ Update Buttons -->
        <div class="d-flex justify-content-between mt-3">

            <!-- ❗ Delete Button - Separate Form -->
            {{-- <form action="{{ route('email-templates.destroy', $template->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.');" class="">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i> Delete Template
                </button>
            </form> --}}

            <!-- ✅ Save Buttons -->
            <div class="pull-right">
                <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Update Template</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

<script>
    // Initialize Summernote
    $('#emailEditor').summernote({
        height: 200
    });

    // Insert Merge Tags safely
    function insertTag(tag) {
        $('#emailEditor').summernote('pasteHTML', `@{{ ${tag} }}`);
    }
</script>
@endpush
