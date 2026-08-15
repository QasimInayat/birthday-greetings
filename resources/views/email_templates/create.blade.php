@extends('layouts.scaffold')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
@endpush
@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-plus me-2"></i> Add Email Template</h3>

    <form action="{{ route('email-templates.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Template Name -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Template Name *</label>
                <input type="text" name="template_name" class="form-control" value="{{ old('template_name') }}" required>
                @error('template_name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Template Type -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Template Type *</label>
                <select name="template_type" class="form-select" required>
                    <option value="" disabled selected>Select type</option>
                    @foreach ($templateTypes as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                </select>
                @error('template_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Subject -->
            <div class="col-md-12 mb-3">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                @error('subject')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Merge Tags -->
            <div class="col-md-12 mb-2">
                <label class="form-label">Insert Merge Tags:</label>
                <div class="btn-group mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="insertTag('employee_name')">
                        Employee Name
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="insertTag('department')">
                        Department
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="insertTag('birthday_date')">
                        Birthday
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="insertTag('company_name')">
                        Company Name
                    </button>

                </div>
            </div>

            <!-- Content (Summernote Editor) -->
            <div class="col-md-12 mb-3">
                <label class="form-label">Email Content *</label>
                <textarea id="emailEditor" name="content">{{ old('content') }}</textarea>
                @error('content')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Save Template</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script>
    // Initialize Summernote
    $('#emailEditor').summernote({
        height: 200,
        placeholder: 'Write your email template here...'
    });

    // Insert Merge Tag in Editor
     function insertTag(tag) {
        $('#emailEditor').summernote('pasteHTML', `@{{ ${tag} }}`);
    }
</script>
@endpush
