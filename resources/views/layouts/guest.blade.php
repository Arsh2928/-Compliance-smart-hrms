<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ComplianceSys')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #fff7cc 0%, #fef3c7 40%, #f8f9fb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Inter', sans-serif;
        }
        .auth-card {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(250,204,21,0.25);
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            padding: 2.5rem 2.25rem;
            width: 100%;
            max-width: 420px;
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.75rem;
            text-decoration: none;
        }
        .auth-logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #facc15, #f59e0b);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #0f172a;
            box-shadow: 0 4px 12px rgba(250,204,21,0.35);
        }
        .auth-logo-text {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
        }
        .auth-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .auth-subtitle {
            font-size: 0.84rem;
            color: #64748b;
            margin-bottom: 1.75rem;
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a href="/" class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-shield-check"></i></div>
            <span class="auth-logo-text">ComplianceSys</span>
        </a>
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
