@extends('layouts.scaffold')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-clipboard-list me-2"></i> Communication Logs</h3>
    </div>

    <!-- Search + Type Filter -->
    <form method="GET" action="{{ route('logs.index') }}" class="mb-3">
        <div class="input-group">
            <select name="type" class="form-select" style="max-width:160px">
                <option value="">All types</option>
                <option value="sms" {{ $filter === 'sms' ? 'selected' : '' }}>SMS</option>
                <option value="email" {{ $filter === 'email' ? 'selected' : '' }}>Email</option>
            </select>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search recipient, subject or message...">
            <button class="btn btn-secondary"><i class="fa fa-search"></i></button>
            <a href="{{ route('logs.index') }}" type="button" class="btn btn-danger"><i class="fa fa-times"></i></a>
        </div>
    </form>

    <!-- Logs Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th width="80">Type</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th width="90">Status</th>
                        <th width="160">Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>
                                @if($log->type === 'sms')
                                    <span class="badge bg-info text-dark">SMS</span>
                                @else
                                    <span class="badge bg-primary">Email</span>
                                @endif
                            </td>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ $log->subject ?? '-' }}</td>
                            <td class="text-truncate" style="max-width:320px" title="{{ $log->message }}">
                                {{ $log->message }}
                            </td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    @endforeach

                    @if($logs->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted">No logs found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
