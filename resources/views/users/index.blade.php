@extends('layouts.scaffold')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fa-solid fa-user-shield me-2"></i> Portal Users</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add User
        </a>
    </div>

    <p class="text-muted">
        These are the accounts that can sign in and administer the portal. Public sign-up is disabled,
        so accounts can only be created here.
    </p>

    <!-- Search -->
    <form method="GET" action="{{ route('users.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search name or email...">
            <button class="btn btn-secondary"><i class="fa fa-search"></i></button>
            <a href="{{ route('users.index') }}" class="btn btn-danger"><i class="fa fa-times"></i></a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Added</th>
                            <th width="130">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-primary ms-1">You</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    @if($user->id !== auth()->id() && $users->total() > 1)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete {{ $user->name }}? They will lose access immediately.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-danger" disabled
                                                title="{{ $user->id === auth()->id() ? 'You cannot delete your own account' : 'The only account cannot be deleted' }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if($users->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted">No users found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
