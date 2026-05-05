<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ComplianceSys</title>
    <meta name="description" content="ComplianceSys – Premium Labour Law Compliance Management">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Custom CSS (no Vite) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

{{-- Mobile overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-wrapper">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">

        {{-- Brand --}}
        <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <span>ComplianceSys</span>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            @auth
            @php $role = auth()->user()->role; @endphp

            <p class="sidebar-section-label">Quick</p>
            <a href="{{ route('home') }}"
               class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i> Home
            </a>

            @if($role === 'admin')
                <p class="sidebar-section-label">Management</p>
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a href="{{ route('admin.employees.index') }}"
                   class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Employees
                </a>
                <a href="{{ route('admin.leaves.index') }}"
                   class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-x-fill"></i> Leave Requests
                </a>
                <a href="{{ route('admin.complaints.index') }}"
                   class="nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-octagon-fill"></i> Complaints
                </a>
                <p class="sidebar-section-label">Finance</p>
                <a href="{{ route('admin.payrolls.index') }}"
                   class="nav-link {{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> Payroll
                </a>
                <a href="{{ route('admin.contracts.index') }}"
                   class="nav-link {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Contracts
                </a>

            @elseif($role === 'hr')
                <p class="sidebar-section-label">HR Panel</p>
                <a href="{{ route('hr.dashboard') }}"
                   class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a href="{{ route('hr.employees.index') }}"
                   class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Employees
                </a>
                <a href="{{ route('hr.leaves.index') }}"
                   class="nav-link {{ request()->routeIs('hr.leaves.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-x-fill"></i> Leave Approvals
                </a>
                <a href="{{ route('hr.complaints.index') }}"
                   class="nav-link {{ request()->routeIs('hr.complaints.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-octagon-fill"></i> Complaints
                </a>
                <p class="sidebar-section-label">Finance</p>
                <a href="{{ route('hr.payrolls.index') }}"
                   class="nav-link {{ request()->routeIs('hr.payrolls.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> Payroll
                </a>

            @elseif($role === 'employee')
                <p class="sidebar-section-label">My Workspace</p>
                <a href="{{ route('employee.dashboard') }}"
                   class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a href="{{ route('employee.leaves.index') }}"
                   class="nav-link {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-x-fill"></i> My Leaves
                </a>
                <a href="{{ route('employee.complaints.index') }}"
                   class="nav-link {{ request()->routeIs('employee.complaints.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-octagon-fill"></i> Grievances
                </a>
                <a href="{{ route('employee.payrolls.index') }}"
                   class="nav-link {{ request()->routeIs('employee.payrolls.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> My Payslips
                </a>
            @endif
            @endauth
        </nav>

        {{-- Sidebar Footer --}}
        @auth
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3 mb-3 px-1">
                <div class="topbar-avatar flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ auth()->user()->name }}
                    </div>
                    <div style="font-size:0.70rem;color:#64748b;text-transform:capitalize;">
                        {{ auth()->user()->role }}
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" style="color:#64748b;">
                    <i class="bi bi-box-arrow-left"></i> Sign Out
                </button>
            </form>
        </div>
        @endauth
    </aside>
    {{-- ===== /SIDEBAR ===== --}}

    {{-- ===== MAIN AREA ===== --}}
    <div class="main-content">

        {{-- Topbar --}}
        <header class="topbar">
            {{-- Left: hamburger + title --}}
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-dark-outline d-lg-none p-2" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="bi bi-list" style="font-size:1.15rem;"></i>
                </button>
                <h5 class="topbar-title">@yield('title', 'Dashboard')</h5>
            </div>

            {{-- Right: actions --}}
            <div class="topbar-actions">
                @auth
                {{-- Date chip --}}
                <span class="d-none d-md-flex pill-tag">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->format('D, d M Y') }}
                </span>

                {{-- Notification Bell --}}
                @php
                    try {
                        $unreadAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(6)->get();
                        $unreadCount  = $unreadAlerts->count();
                    } catch(\Exception $e) {
                        $unreadAlerts = collect();
                        $unreadCount  = 0;
                    }
                @endphp
                <div class="dropdown">
                    <button class="notif-btn" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill"></i>
                        @if($unreadCount > 0)
                            <span class="notif-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px;max-height:360px;overflow-y:auto;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        @forelse($unreadAlerts as $alert)
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('alerts.read', $alert->id) }}">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-{{ $alert->type === 'danger' ? 'x-circle-fill text-danger' : ($alert->type === 'success' ? 'check-circle-fill text-success' : 'exclamation-circle-fill text-warning') }} mt-1"></i>
                                    <div>
                                        <div style="font-size:0.82rem;white-space:normal;">{{ $alert->message }}</div>
                                        <div class="text-muted" style="font-size:0.70rem;">{{ $alert->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @empty
                        <li><span class="dropdown-item text-muted text-center py-3" style="font-size:0.82rem;">All caught up! 🎉</span></li>
                        @endforelse
                    </ul>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <button class="btn d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            style="background:#f8f9fb;border:1px solid var(--border);border-radius:40px;padding:5px 12px 5px 6px;">
                        <div class="topbar-avatar" style="width:30px;height:30px;font-size:0.72rem;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span style="font-size:0.82rem;font-weight:600;color:#0f172a;">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                        <i class="bi bi-chevron-down" style="font-size:0.62rem;color:#64748b;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div style="font-weight:700;font-size:0.875rem;">{{ auth()->user()->name }}</div>
                            <div style="font-size:0.72rem;color:#64748b;text-transform:capitalize;">{{ auth()->user()->role }}</div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-circle me-2 text-muted"></i> Profile Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-left me-2"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-area">
            @yield('content')
        </main>
    </div>
    {{-- ===== /MAIN AREA ===== --}}

</div>

{{-- Bootstrap 5 Bundle JS (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- SweetAlert2 (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
{{-- Chart.js (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Mobile Sidebar Toggle ── */
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar()  { sidebar?.classList.add('open');   overlay?.classList.add('active'); }
    function closeSidebar() { sidebar?.classList.remove('open');overlay?.classList.remove('active'); }

    toggle?.addEventListener('click', () => sidebar?.classList.contains('open') ? closeSidebar() : openSidebar());
    overlay?.addEventListener('click', closeSidebar);

    /* ── Flash Toasts ── */
    @if(session('success'))
    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, timerProgressBar:true })
        .fire({ icon:'success', title: @json(session('success')) });
    @endif
    @if(session('error'))
    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:4000, timerProgressBar:true })
        .fire({ icon:'error', title: @json(session('error')) });
    @endif
    @if(session('warning'))
    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:4000, timerProgressBar:true })
        .fire({ icon:'warning', title: @json(session('warning')) });
    @endif
});
</script>

@stack('scripts')
</body>
</html>
