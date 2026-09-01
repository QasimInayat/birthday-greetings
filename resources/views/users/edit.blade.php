@extends('layouts.scaffold')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fa-solid fa-user-pen me-2"></i> Edit User</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password"
                               placeholder="Leave blank to keep current password">
                        <small class="text-muted">Only fill this in to reset their password.</small>
                        @error('password')<div><small class="text-danger">{{ $message }}</small></div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button class="btn btn-success"><i class="fa-solid fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
