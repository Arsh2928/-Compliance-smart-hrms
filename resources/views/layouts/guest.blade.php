<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ComplianceSys')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
<main class="min-vh-100 d-flex align-items-center bg-gray-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-7 col-lg-5">
                <div class="card shadow-lg border-0 my-5">
                    <div class="card-body p-4 p-sm-5">
                        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:40px;height:40px;background:rgba(94,114,228,.12);">
                                <i class="bi bi-shield-check text-primary"></i>
                            </div>
                            <span class="font-weight-bolder text-dark">ComplianceSys</span>
                        </a>

        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
                    </div>
                </div>
                <p class="text-center text-secondary text-sm mb-0">
                    © {{ date('Y') }} ComplianceSys
                </p>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js"></script>
</body>
</html>
