<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'AsaWatch Admin')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin/assets/css/asawatch.css') }}">
</head>
<body>

@include('admin.partials.nav-offcanvas')

<div class="hw-shell">

@include('admin.partials.sidebar')

  <main class="hw-main">

  @include('admin.partials.header')

    <div class="hw-content d-flex flex-column gap-4">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      @yield('content')
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('admin/assets/js/asawatch.js') }}"></script>
@stack('scripts')
</body>
</html>
