<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Law Compliance - @yield('title', 'Home')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="bg-gray-100">
    <nav class="navbar navbar-expand-lg position-sticky top-0 z-index-sticky blur shadow-blur py-2 landing-navbar">
        <div class="container">
            <a class="navbar-brand font-weight-bolder" href="{{ route('home') }}">
                <i class="bi bi-shield-check me-1 text-primary"></i> ComplianceSys
            </a>
            <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon mt-2">
                    <span class="navbar-toggler-bar bar1"></span>
                    <span class="navbar-toggler-bar bar2"></span>
                    <span class="navbar-toggler-bar bar3"></span>
                </span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('features') ? 'active' : '' }}" href="{{ route('features') }}">Features</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                </ul>

                <div class="d-flex align-items-center ms-lg-3 mt-3 mt-lg-0 gap-2">
                    <button type="button" class="ui-theme-toggle landing-theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                        <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
                    </button>
                    @auth
                        <a class="btn bg-gradient-primary btn-sm mb-0" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm mb-0">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    @else
                        <a class="btn btn-outline-primary btn-sm mb-0" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a class="btn bg-gradient-primary btn-sm mb-0" href="{{ route('register') }}">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="min-vh-100">
        @yield('content')
    </main>

    <footer class="py-4 mt-5">
        <div class="container">
            <p class="text-secondary mb-0">&copy; {{ date('Y') }} ComplianceSys. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>
