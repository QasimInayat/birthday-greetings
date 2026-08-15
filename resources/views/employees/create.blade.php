@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-user-plus me-2"></i> Add New Employee</h3>

    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <!-- Profile Preview -->
            <div class="col-md-12 text-center mb-4">
                <x-avatar id="previewImg" :size="100" class="rounded-circle border" />
                <div class="mt-2">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="profile_image" class="form-control w-50 mx-auto"
                           onchange="previewFile(this)">
                    @error('profile_image')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- FORM FIELDS START -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                @error('full_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="{{ old('department') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Designation</label>
                <input type="text" name="designation" class="form-control" value="{{ old('designation') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Birthday *</label>
                <input type="date" name="birthday" class="form-control" value="{{ old('birthday') }}" required>
                @error('birthday') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select" required>
                    <option value="" disabled selected>Select gender</option>
                    <option value="Male" {{ old('gender')=='Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender')=='Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender')=='Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Save Employee</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Preview profile image before upload
    function previewFile(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('previewImg').src = reader.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
