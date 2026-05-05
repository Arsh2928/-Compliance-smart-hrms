<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Dashboard') - ComplianceSys</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stack('styles')
</head>

<body class="g-sidenav-show g-sidenav-pinned bg-gray-100">

@include('layouts.sidebar')
<div class="ui-sidebar-overlay" id="sidebarOverlay"></div>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  @include('layouts.navbar')

  @if(session('success') || session('error'))
  <div class="container-fluid px-3 px-lg-4 mt-2">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
  </div>
  @endif

  <div class="container-fluid py-4 px-3 px-lg-4">
    @yield('content')
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js"></script>
<script>
  (function () {
    const storageKey = 'compliancesys-theme';
    const root = document.documentElement;
    const toggle = document.getElementById('themeToggle');
    const icon = document.getElementById('themeToggleIcon');
    const savedTheme = localStorage.getItem(storageKey);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    function applyTheme(theme) {
      root.setAttribute('data-theme', theme);
      if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
      }
    }

    applyTheme(savedTheme || (prefersDark ? 'dark' : 'light'));

    if (toggle) {
      toggle.addEventListener('click', function () {
        const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem(storageKey, nextTheme);
        applyTheme(nextTheme);
      });
    }
  })();

  (function () {
    const body = document.body;
    const sidebar = document.getElementById('sidenav-main');
    const toggles = [
      document.getElementById('iconNavbarSidenav'),
      document.getElementById('iconSidenav')
    ].filter(Boolean);
    const overlay = document.getElementById('sidebarOverlay');

    function isMobileLayout() {
      return window.matchMedia('(max-width: 1199px)').matches;
    }

    function closeSidebar() {
      body.classList.remove('ui-sidebar-open');
    }

    function toggleSidebar(event) {
      if (!isMobileLayout()) {
        return;
      }

      event.preventDefault();
      body.classList.toggle('ui-sidebar-open');
    }

    toggles.forEach(toggle => toggle.addEventListener('click', toggleSidebar));
    overlay?.addEventListener('click', closeSidebar);
    sidebar?.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', closeSidebar));
    window.addEventListener('resize', function () {
      if (!isMobileLayout()) {
        closeSidebar();
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeSidebar();
      }
    });
  })();
</script>
@stack('scripts')
</body>
</html>
