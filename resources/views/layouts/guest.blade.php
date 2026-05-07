<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ComplianceSys')</title>
    <script>
        (function () {
            const storageKey = 'compliancesys-theme';
            const savedTheme = localStorage.getItem(storageKey);
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', savedTheme || (prefersDark ? 'dark' : 'light'));
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="auth-body {{ request()->routeIs('register') ? 'auth-register-page' : '' }}">
<main class="auth-shell min-vh-100">
    <div class="auth-card">
        <div class="auth-art-panel">
            <div class="auth-art-top">
                <a href="{{ route('home') }}" class="auth-brand">
                    <i class="bi bi-shield-check"></i>
                    <span>ComplianceSys</span>
                </a>
                <a href="{{ route('home') }}" class="auth-back-link">
                    Back to website
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>

            <div class="auth-slide-track" aria-hidden="true">
                <div class="auth-slide auth-slide-one">
                    <div class="auth-slide-caption">
                        <h2>Capturing compliance, creating clarity.</h2>
                        <p>Keep attendance, leaves, payroll, and contracts visible from one workspace.</p>
                    </div>
                </div>
                <div class="auth-slide auth-slide-two">
                    <div class="auth-slide-caption">
                        <h2>HR workflows that stay organised.</h2>
                        <p>Review requests, track alerts, resolve grievances, and keep records audit-ready.</p>
                    </div>
                </div>
                <div class="auth-slide auth-slide-three">
                    <div class="auth-slide-caption">
                        <h2>Recognition built into daily work.</h2>
                        <p>Use scores, leaderboards, and rewards to keep employees engaged.</p>
                    </div>
                </div>
            </div>

            <div class="auth-art-scene" aria-hidden="true">
                <span class="auth-orbit auth-orbit-one"></span>
                <span class="auth-orbit auth-orbit-two"></span>
            </div>

            <div class="auth-art-dots" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div class="auth-form-panel">
            <a href="{{ route('home') }}" class="auth-mobile-back">
                <i class="bi bi-arrow-left"></i>
                Back to home
            </a>

            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset

            <p class="auth-copyright">
                &copy; {{ date('Y') }} ComplianceSys
            </p>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js"></script>
</body>
</html>
