<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Birthday Manager' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">

    <!-- Custom Global Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Page specific CSS -->
    @stack('styles')
</head>
<body>
<!-- Sidebar Placeholder -->
{{-- Sidebar will be included here --}}
{{-- Example: @include('partials.sidebar') --}}

<!-- Header Placeholder -->
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">🎂 Birthday Manager</span>

        <div class="d-flex align-items-center">
            <!-- Dark Mode Toggle -->
            <button id="themeToggleBtn" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fa-solid fa-moon"></i>
            </button>

            <!-- User Menu Placeholder -->
            {{-- User Profile dropdown can go here --}}
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user"></i> User
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">Logout</button>
                    </form></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Page Content Wrapper -->
<div class="container py-4">
    @yield('content')
</div>
<!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    @if(session('success'))
        <div class="toast align-items-center text-bg-success border-0 show">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="toast align-items-center text-bg-danger border-0 show">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
</div>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Summernote -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

<!-- Global Scripts -->
<script>
    // Light/Dark Theme Toggle
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    themeToggleBtn?.addEventListener('click', function () {
        const html = document.documentElement;
        html.dataset.bsTheme = html.dataset.bsTheme === 'light' ? 'dark' : 'light';
    });
</script>

<!-- Page specific scripts -->
@stack('scripts')
</body>
</html>
