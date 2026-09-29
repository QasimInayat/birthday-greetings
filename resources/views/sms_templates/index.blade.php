@extends('layouts.scaffold')

@section('content')
<div class="container">

    <!-- Page Title + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-message me-2"></i> SMS Templates</h3>
        <a href="{{ route('sms-templates.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add SMS Template
        </a>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('sms-templates.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search templates...">
            <button class="btn btn-secondary"><i class="fa fa-search"></i></button>
            <a href="{{route('sms-templates.index')}}" class="btn btn-danger"><i class="fa fa-times"></i></a>

        </div>
    </form>

    <!-- SMS Templates Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th width="120">Type</th>
                        <th>Message Preview</th>
                        <th width="90">Characters</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr>
                            <td>
                                {{ $template->template_name }}
                                @if($template->is_default)
                                    <span class="badge bg-success ms-1" title="Sent automatically for this event">Default</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-body-secondary text-body border">
                                    {{ $templateTypes[$template->template_type] ?? ucfirst($template->template_type) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($template->message, 50) }}</td>
                            <td>{{ strlen($template->message) }}</td>
                            <td>
                                <!-- Make default -->
                                @unless($template->is_default)
                                    <form action="{{ route('sms-templates.default', $template->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-outline-success btn-sm" title="Make this the default for {{ $templateTypes[$template->template_type] ?? $template->template_type }}">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </form>
                                @endunless

                                <!-- Preview -->
                                <button class="btn btn-info btn-sm" onclick="previewSms({{ $template->id }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- Edit -->
                                <a href="{{ route('sms-templates.edit', $template->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('sms-templates.destroy', $template->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($templates->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted">No SMS templates found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewSmsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="smsPreviewBody">
                <!-- Message loads here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewSms(id) {
        fetch(`/sms-templates/preview/${id}`)
            .then(response => response.json())
            .then(data => {
                const box = document.getElementById('smsPreviewBody');
                box.innerHTML = '';

                if (data.rendered) {
                    const heading = document.createElement('div');
                    heading.className = 'small text-muted mb-1';
                    heading.textContent = 'As ' + data.sample + ' would receive it:';

                    const sent = document.createElement('div');
                    sent.className = 'border rounded p-2 mb-3';
                    sent.textContent = data.rendered;

                    const meta = document.createElement('div');
                    meta.className = 'small text-muted mb-3';
                    meta.textContent = data.length + ' characters · ' + data.segments +
                        ' SMS segment' + (data.segments > 1 ? 's' : '') +
                        (data.segments > 1 ? ' — this will be billed as ' + data.segments + ' messages' : '');

                    const rawHeading = document.createElement('div');
                    rawHeading.className = 'small text-muted mb-1';
                    rawHeading.textContent = 'Template as written:';

                    const raw = document.createElement('div');
                    raw.className = 'border rounded p-2 bg-body-secondary small font-monospace';
                    raw.textContent = data.message;

                    box.append(heading, sent, meta, rawHeading, raw);
                } else {
                    box.textContent = data.message;

                    if (data.note) {
                        const note = document.createElement('div');
                        note.className = 'small text-muted mt-2';
                        note.textContent = data.note;
                        box.append(note);
                    }
                }

                new bootstrap.Modal(document.getElementById('previewSmsModal')).show();
            });
    }
</script>
@endpush

