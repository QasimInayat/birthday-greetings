@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-user-plus me-2"></i> Add User</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        <small class="text-muted">This is the login username.</small>
                        @error('email')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        <small class="text-muted">Minimum 8 characters.</small>
                        @error('password')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button class="btn btn-success"><i class="fa-solid fa-save"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
