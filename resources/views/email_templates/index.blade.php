@extends('layouts.scaffold')

@section('content')
<div class="container">

    <!-- Page Title + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-envelope-open-text me-2"></i> Email Templates</h3>
        <a href="{{ route('email-templates.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Template
        </a>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('email-templates.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search templates...">
            <button class="btn btn-secondary"><i class="fa fa-search"></i></button>
            <a href="{{route('email-templates.index')}}" class="btn btn-danger"><i class="fa fa-times"></i></a>

        </div>
    </form>

    <!-- Templates Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr>
                            <td>{{ $template->template_name }}</td>
                            <td>{{ ucfirst($template->template_type) }}</td>
                            <td>{{ $template->subject }}</td>
                            <td>
                                <!-- Preview -->
                                <button class="btn btn-info btn-sm" onclick="previewTemplate({{ $template->id }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- Edit -->
                                <a href="{{ route('email-templates.edit', $template->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('email-templates.destroy', $template->id) }}" method="POST" class="d-inline"
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
                            <td colspan="4" class="text-center text-muted">No templates found.</td>
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
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Content loads here with AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preview Template AJAX
    function previewTemplate(id) {
        fetch(`/email-templates/preview/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('previewContent').innerHTML = data.content;
                new bootstrap.Modal(document.getElementById('previewModal')).show();
            });
    }
</script>
@endpush
