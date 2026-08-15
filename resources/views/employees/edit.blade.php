@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-user-pen me-2"></i> Edit Employee</h3>

    <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <!-- Current Profile Image -->
            <div class="col-md-12 text-center mb-4">
                <x-avatar id="previewImg" :src="$employee->profile_image" :name="$employee->full_name"
                          :size="100" class="rounded-circle border" />
                <div class="mt-2">
                    <label class="form-label">Change Profile Image</label>
                    <input type="file" name="profile_image" class="form-control w-50 mx-auto" onchange="previewFile(this)">
                    
                </div>
            </div>

            <!-- FORM FIELDS START -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $employee->full_name) }}" required>
                @error('full_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Designation</label>
                <input type="text" name="designation" class="form-control" value="{{ old('designation', $employee->designation) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Birthday *</label>
                <input type="date" name="birthday" class="form-control" value="{{ old('birthday', $employee->birthday) }}" required>
                @error('birthday') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select" required>
                    <option value="Male" {{ $employee->gender == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ $employee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ $employee->gender == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-success">Update Employee</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Preview profile image
    function previewFile(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = () => document.getElementById('previewImg').src = reader.result;
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
