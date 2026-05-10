<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Unavailable | Smart HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .error-box { max-width: 520px; text-align: center; }
        .db-icon { font-size: 5rem; background: linear-gradient(135deg, #ef4444, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .error-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; }
        .badge-pulse { display: inline-block; background: #fef3c7; color: #92400e; border-radius: 2rem; padding: .35rem 1rem; font-size: .85rem; font-weight: 600; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{ opacity:1; } 50%{ opacity:.5; } }
        .retry-btn { background: linear-gradient(135deg,#4f46e5,#3b82f6); border: none; border-radius: .75rem; color: #fff; padding: .7rem 2rem; font-size: 1rem; font-weight: 600; cursor: pointer; transition: transform .15s; }
        .retry-btn:hover { transform: scale(1.03); color: #fff; }
        .detail-card { background: #fff; border-radius: 1rem; padding: 1.25rem 1.5rem; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,.06); }
    </style>
</head>
<body>
    <div class="error-box p-4">
        <div class="db-icon mb-3"><i class="bi bi-database-x"></i></div>

        <span class="badge-pulse mb-3 d-inline-block">
            <i class="bi bi-wifi-off me-1"></i> Database Unreachable
        </span>

        <h1 class="error-title mt-3 mb-2">Cannot Connect to Database</h1>
        <p class="text-muted mb-4">
            Smart HRMS is temporarily unable to reach its MongoDB database.
            This is usually caused by a network interruption or the database service being momentarily unavailable.
        </p>

        <div class="detail-card mb-4">
            <p class="mb-2 text-muted small fw-semibold text-uppercase">What you can do</p>
            <ul class="mb-0 ps-3 text-secondary small">
                <li>Check your internet connection.</li>
                <li>Wait a few seconds and retry — the database may be waking up.</li>
                <li>If the issue persists, contact the system administrator.</li>
            </ul>
        </div>

        <a href="{{ url()->current() }}" class="btn retry-btn">
            <i class="bi bi-arrow-clockwise me-2"></i>Retry Now
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
