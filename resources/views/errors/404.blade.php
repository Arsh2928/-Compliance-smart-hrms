<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Not Found | ComplianceSys</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-box { max-width: 480px; text-align: center; }
        .error-code { font-size: 7rem; font-weight: 800; line-height: 1; background: linear-gradient(135deg,#4f46e5,#3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="error-box p-4">
        <div class="error-code">404</div>
        <h2 class="fw-bold mt-2 mb-2">Page Not Found</h2>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-house-fill me-2"></i>Back to Dashboard
        </a>
        @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </a>
        @endauth
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
