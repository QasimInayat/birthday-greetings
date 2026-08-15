<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Employee Birthday Manager</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css')}}">
  @stack('styles')
</head>
<body>

  <!-- Sidebar -->
  @include('partials.sidebar')
  <!-- Main Content -->
  <div class="main-content">

    <!-- Top Navbar -->
    @include('partials.navbar')
    <div class="mt-5">
    <div class="container">
      @include('partials.flash')
    </div>
    @yield('content')
    </div>
  </div>

  <!-- JS Files -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/dashboard.js')}}"></script>


  @stack('scripts')
</body>
</html>
