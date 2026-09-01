@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-user-gear me-2"></i> My Profile</h3>

    <div class="row">
        <!-- Account details -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Account Details</h5>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            <small class="text-muted">You sign in with this address.</small>
                            @error('email')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success"><i class="fa-solid fa-save"></i> Save Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Change Password</h5>

                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password" required>
                            @error('current_password')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                            <small class="text-muted">Minimum 8 characters.</small>
                            @error('password')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success"><i class="fa-solid fa-key"></i> Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
