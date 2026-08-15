@extends('layouts.scaffold')

@section('content')
<div class="container">

    <h3 class="mb-4"><i class="fa-solid fa-file-import me-2"></i> Bulk Upload Employees</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('employees.bulkUpload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Sample Download -->
                <div class="mb-3">
                    <a href="{{ route('employees.bulkSample') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-file-arrow-down"></i> Download Sample File
                    </a>
                </div>

                <!-- File Input -->
                <div class="mb-3">
                    <label class="form-label">Upload CSV File *</label>
                    <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    @error('file')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Import Rules -->
                <div class="alert alert-info small">
                    <strong>Note:</strong>
                    <ul class="mb-0 ps-3">
                        <li>Allowed file type: .csv (save Excel files as CSV first)</li>
                        <li>Required columns: <code>full_name</code>, <code>email</code>, <code>birthday</code></li>
                        <li>Optional columns: <code>phone</code>, <code>department</code>, <code>designation</code>, <code>gender</code></li>
                        <li>Phone numbers should be in international format, e.g. <code>2348012345678</code></li>
                        <li>Maximum 100 records per upload</li>
                        <li>Duplicate emails are skipped automatically</li>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Import Employees</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
