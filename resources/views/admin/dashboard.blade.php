@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<style>
/* Dashboard Container System */
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}



/* Standard Card System */
.dashboard-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(226, 232, 240, 0.6);
}

.dashboard-card.h-100 {
    margin-bottom: 0;
}

/* Card Header Override */
.dashboard-card .card-header {
    background: transparent;
    border-bottom: none;
    padding: 0 0 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-card .card-body {
    padding: 0;
}

/* Stats Grid System */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

/* Main Content Layout */
.container-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    align-items: stretch;
    margin-bottom: 20px;
}

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .container-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Quick Actions Inner Card System */
.quick-action-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 16px;
    background: #f8fafc;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    border: 1px solid #f1f5f9;
}

.quick-action-item:hover {
    background: #ffffff;
    border-color: #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

/* Typography & Layout Fixes */
.welcome-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.25rem;
}
.welcome-subtitle {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 0;
}
.card-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #0f172a;
    font-size: 1.05rem;
}
</style>

<div class="dashboard-container">

    {{-- Header Card --}}
    <div class="dashboard-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3" style="padding: 28px 32px;">
        <div>
            <h2 class="welcome-title">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
                {{ explode(' ', auth()->user()->name)[0] }} 👋
            </h2>
            <p class="welcome-subtitle">Here's what's happening with your team today — {{ now()->format('l, d F Y') }}</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-4 py-2" style="background-color: #f59e0b; border-color: #f59e0b; color: #fff; font-weight: 600; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);">
            <i class="bi bi-person-plus-fill"></i>
            <span>Add Employee</span>
        </a>
    </div>

    {{-- 🔥 Top Performers This Month --}}
    <h5 class="mb-3" style="font-weight: 800; color: #0f172a;">🔥 Top Performers This Month</h5>
    <div class="stats-grid mb-4" style="grid-template-columns: repeat(3, 1fr);">
        @forelse($topPerformers ?? [] as $index => $performer)
        <div class="dashboard-card h-100 d-flex align-items-center gap-3" style="padding: 16px 20px;">
            <div class="topbar-avatar flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.2rem; background: {{ $index == 0 ? 'linear-gradient(135deg, #fbbf24, #d97706)' : ($index == 1 ? 'linear-gradient(135deg, #94a3b8, #475569)' : 'linear-gradient(135deg, #d97706, #9a3412)') }}; color: #fff;">
                {{ substr($performer->user->name ?? 'A', 0, 1) }}
            </div>
            <div style="flex-grow: 1;">
                <div class="fw-bold" style="font-size: 1.05rem; color: #0f172a;">{{ $performer->user->name ?? 'Unknown' }}</div>
                <div class="text-muted" style="font-size:0.85rem;">Score: <span class="fw-bold" style="color: #f59e0b;">{{ $performer->performance_score ?? 0 }}</span></div>
            </div>
            <div class="fs-2">
                @php $badges = $performer->badges ?? []; $highest = end($badges); @endphp
                {{ $highest === 'Gold' ? '🥇' : ($highest === 'Silver' ? '🥈' : ($highest === 'Bronze' ? '🥉' : '🚀')) }}
            </div>
        </div>
        @empty
        <div class="dashboard-card h-100 d-flex align-items-center justify-content-center text-muted" style="grid-column: span 3; padding: 20px;">
            No top performers ranked yet. Monthly evaluation pending!
        </div>
        @endforelse
    </div>

    {{-- ⚠️ Needs Attention (Low Performers) --}}
    <h5 class="mb-3 mt-4" style="font-weight: 800; color: #ef4444;">⚠️ Needs Attention</h5>
    <div class="stats-grid mb-4" style="grid-template-columns: repeat(3, 1fr);">
        @forelse($lowPerformers ?? [] as $performer)
        <div class="dashboard-card h-100 d-flex align-items-center gap-3" style="padding: 16px 20px; border-left: 4px solid #ef4444;">
            <div class="topbar-avatar flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.2rem; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                {{ substr($performer->user->name ?? 'A', 0, 1) }}
            </div>
            <div style="flex-grow: 1;">
                <div class="fw-bold" style="font-size: 1.05rem; color: #0f172a;">{{ $performer->user->name ?? 'Unknown' }}</div>
                <div class="text-danger" style="font-size:0.85rem;">Score: <span class="fw-bold">{{ $performer->performance_score ?? 0 }}</span></div>
            </div>
            <div>
                <a href="{{ route('admin.employees.show', $performer->id) }}" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">Review</a>
            </div>
        </div>
        @empty
        <div class="dashboard-card h-100 d-flex align-items-center justify-content-center text-muted" style="grid-column: span 3; padding: 20px;">
            <i class="bi bi-check-circle text-success me-2"></i> All employees are performing well. No critical alerts.
        </div>
        @endforelse
    </div>

    {{-- Stat Cards Grid --}}
    <div class="stats-grid">
        <div class="dashboard-card h-100">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $totalEmployees }}</div>
                    <div class="stat-label">Total Employees</div>
                    <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Active workforce</div>
                </div>
            </div>
        </div>
        <div class="dashboard-card h-100">
            <div class="stat-card">
                <div class="stat-icon success"><i class="bi bi-calendar-check-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $attendanceToday }}</div>
                    <div class="stat-label">Present Today</div>
                    <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Checked in</div>
                </div>
            </div>
        </div>
        <div class="dashboard-card h-100">
            <div class="stat-card">
                <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value">{{ $pendingLeaves }}</div>
                    <div class="stat-label">Pending Leaves</div>
                    @if($pendingLeaves > 0)
                    <div class="stat-trend down"><i class="bi bi-clock"></i> Needs review</div>
                    @else
                    <div class="stat-trend up"><i class="bi bi-check2"></i> All clear</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="dashboard-card h-100">
            <div class="stat-card">
                <div class="stat-icon danger"><i class="bi bi-exclamation-octagon-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $openComplaints }}</div>
                    <div class="stat-label">Open Complaints</div>
                    @if($openComplaints > 0)
                    <div class="stat-trend down"><i class="bi bi-exclamation"></i> Unresolved</div>
                    @else
                    <div class="stat-trend up"><i class="bi bi-check2-all"></i> All resolved</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid: Chart + Quick Actions --}}
    <div class="container-row">
        {{-- Attendance Chart --}}
        <div>
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="stat-icon warning d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <span>Weekly Attendance Trends</span>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Live</span>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="95"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div>
            <div class="dashboard-card h-100">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="stat-icon yellow d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <span>Quick Actions</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <a href="{{ route('admin.employees.create') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(250,204,21,0.15);color:#d97706;">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">Add New Employee</div>
                            <div style="font-size:0.75rem;color:#64748b;">Onboard a team member</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.leaves.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(245,158,11,0.13);color:#f59e0b;">
                            <i class="bi bi-calendar2-x"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">Review Leaves</div>
                            <div style="font-size:0.75rem;color:#64748b;">Approve or reject requests</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.payrolls.create') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(16,185,129,0.13);color:#10b981;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">Generate Payroll</div>
                            <div style="font-size:0.75rem;color:#64748b;">Process monthly salaries</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.contracts.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(59,130,246,0.13);color:#3b82f6;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">View Contracts</div>
                            <div style="font-size:0.75rem;color:#64748b;">Manage employee contracts</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.complaints.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;">
                            <i class="bi bi-exclamation-octagon"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:#0f172a;">Open Complaints</div>
                            <div style="font-size:0.75rem;color:#64748b;">Resolve grievances</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Compliance Status --}}
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title-group">
                <div class="stat-icon teal d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span>Compliance Overview</span>
            </div>
            <span class="fs-12 text-muted">Updated just now</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-sm-6 col-md-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.85rem;font-weight:600;color:#334155;">Attendance Rate</span>
                            <span style="font-size:0.85rem;font-weight:700;color:#10b981;">
                                {{ $totalEmployees > 0 ? round(($attendanceToday / $totalEmployees) * 100) : 0 }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar" style="width:{{ $totalEmployees > 0 ? round(($attendanceToday / $totalEmployees) * 100) : 0 }}%; background:linear-gradient(90deg,#10b981,#34d399); border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.85rem;font-weight:600;color:#334155;">Leave Approval</span>
                            <span style="font-size:0.85rem;font-weight:700;color:#f59e0b;">
                                {{ $pendingLeaves > 0 ? 'Pending' : '100%' }}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar" style="width:{{ $pendingLeaves > 0 ? 60 : 100 }}%; background:linear-gradient(90deg,#facc15,#f59e0b); border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.85rem;font-weight:600;color:#334155;">Complaint Resolution</span>
                            <span style="font-size:0.85rem;font-weight:700;color:#ef4444;">
                                {{ $openComplaints > 0 ? $openComplaints.' Open' : '100%' }}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar" style="width:{{ $openComplaints > 0 ? 40 : 100 }}%; background:linear-gradient(90deg,#ef4444,#f87171); border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.85rem;font-weight:600;color:#334155;">Overall Score</span>
                            <span style="font-size:0.85rem;font-weight:700;color:#6366f1;">87%</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar" style="width:87%; background:linear-gradient(90deg,#6366f1,#a855f7); border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('attendanceChart');
if (ctx) {
    // Create gradient fills
    const canvas = ctx;
    const gradGreen = canvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradGreen.addColorStop(0, 'rgba(250,204,21,0.35)');
    gradGreen.addColorStop(1, 'rgba(250,204,21,0.0)');

    const gradOrange = canvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradOrange.addColorStop(0, 'rgba(239,68,68,0.25)');
    gradOrange.addColorStop(1, 'rgba(239,68,68,0.0)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDates) !!},
            datasets: [
                {
                    label: 'Employees Present',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: gradGreen,
                    borderColor: '#f59e0b',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45,
                },
                {
                    label: 'Leave Requests',
                    data: {!! json_encode($chartLeaves) !!},
                    backgroundColor: gradOrange,
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.45,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 5,
                        useBorderRadius: true,
                        color: '#64748b',
                        font: { size: 11, weight: '600' },
                        padding: 16,
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#facc15',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(250,204,21,0.3)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: c => ` ${c.dataset.label}: ${c.parsed.y}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.max(...{!! json_encode($chartData) !!}, ...[3]),
                    ticks: {
                        precision: 0,
                        color: '#94a3b8',
                        font: { size: 11 },
                        stepSize: 1,
                    },
                    grid: { color: 'rgba(241,245,249,0.8)' },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });
}
</script>
@endpush

