@extends('layouts.scaffold')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-users me-2"></i> Employees</h3>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Employee
        </a>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('employees.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search employees...">
            <button class="btn btn-secondary"><i class="fa fa-search"></i></button>
            <a  href="{{route('employees.index')}}" type="button" class="btn btn-danger"><i class="fa fa-times"></i></a>
        </div>
    </form>

    <!-- Employees Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>
                                <x-avatar :src="$employee->profile_image" :name="$employee->full_name" :size="40" />
                            </td>
                            <td>{{ $employee->full_name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone ?? '-' }}</td>
                            <td>
                                @if($employee->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($employees->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted">No employees found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
