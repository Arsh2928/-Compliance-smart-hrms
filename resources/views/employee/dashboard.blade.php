@extends('layouts.app')
@section('title', 'My Dashboard')

@section('content')

{{-- Welcome Banner --}}
<div class="welcome-banner mb-4">
    <div>
        <h3 class="welcome-title">Welcome back, {{ explode(' ', $employee->user->name)[0] }}! 👋</h3>
        <p class="welcome-subtitle">{{ $employee->designation }} | {{ $employee->department->name ?? 'No Department' }}</p>
        @if(!empty($employee->badges))
            <div class="mt-2 d-flex gap-2 flex-wrap">
                @foreach($employee->badges as $badge)
                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> {{ $badge }}</span>
                @endforeach
            </div>
        @endif
    </div>
    <div class="d-none d-md-flex align-items-center gap-3">
        <a href="{{ route('employee.leaves.create') }}" class="btn btn-outline-primary shadow-sm">Request Leave</a>
        <form action="{{ route('employee.attendance.checkin') }}" method="POST">
            @csrf
            <button class="btn btn-primary shadow-sm"><i class="bi bi-fingerprint me-1"></i> Check In</button>
        </form>
    </div>
</div>

{{-- Performance & Rating --}}
@if(isset($employee))
@php
    $currentTier = $tierInfo['current_tier'] ?? 'Level 1';
    $tierEmoji = $currentTier === 'Level 5' ? '🥇' : ($currentTier === 'Level 4' ? '🥈' : ($currentTier === 'Level 3' ? '🥉' : '🚀'));
@endphp

{{-- Row 1: Live Score + Tier Progression --}}
<div class="row g-3 mb-3">
    {{-- Live Score Card --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="text-uppercase mb-2 text-dark" style="font-size: 0.72rem; letter-spacing: 1.2px; font-weight: 700;">
                    Live Performance Score <span class="badge bg-primary-light text-primary ms-1">Real-Time</span>
                </h6>
                <div class="d-flex align-items-end gap-2 mb-2">
                    <div class="fw-bold text-dark" style="font-size: 2.5rem; line-height:1;">{{ $liveScore }}</div>
                    <div class="text-secondary" style="font-size:1rem; margin-bottom:4px;">/&nbsp;100</div>
                    <div class="ms-auto fs-2">{{ $tierEmoji }}</div>
                </div>
                @if($latestRecord)
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="text-muted small">Last rank:</span>
                    <span class="badge bg-secondary">#{{ $latestRecord->rank }}</span>
                    @if($latestRecord->rank_delta !== null)
                        @php $d = $latestRecord->rank_delta; @endphp
                        <span class="badge bg-{{ $d > 0 ? 'success' : ($d < 0 ? 'danger' : 'light text-muted') }}">
                            {{ $d > 0 ? '▲' : ($d < 0 ? '▼' : '=') }} {{ abs($d) }}
                        </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tier Progression --}}
    <div class="col-md-7">
        <div class="card h-100" style="border-left: 4px solid #f59e0b;">
            <div class="card-body p-4">
                <h6 class="text-uppercase mb-3" style="font-size:0.72rem; letter-spacing:1.2px; color:#f59e0b;">
                    <i class="bi bi-trophy-fill me-1"></i>Tier Progression
                </h6>
                @if($tierInfo['next_tier'])
                <p class="mb-2 small text-muted">
                    You are <strong>{{ $tierInfo['current_tier'] === 'None' ? 'Unranked' : $tierInfo['current_tier'] }}</strong>.
                    You need <strong class="text-warning">{{ $tierInfo['points_needed'] }} more points</strong> to reach <strong>{{ $tierInfo['next_tier'] }}</strong>.
                </p>
                @php
                    $pct = $tierInfo['points_needed'] > 0
                        ? max(5, min(95, 100 - ($tierInfo['points_needed'] / 30 * 100)))
                        : 100;
                @endphp
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-1" style="font-size:0.72rem; color:#94a3b8;">
                    <span>{{ $tierInfo['current_tier'] }}</span>
                    <span>{{ $tierInfo['next_tier'] }}</span>
                </div>
                @else
                <p class="mb-0 text-success fw-bold"><i class="bi bi-star-fill me-1"></i>You are in the Gold tier! Keep it up.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Component Breakdown + AI Suggestion --}}
<div class="row g-3 mb-4">
    {{-- Score Components --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="text-uppercase mb-3" style="font-size:0.72rem; letter-spacing:1.2px; color:#6366f1;">
                    <i class="bi bi-bar-chart-fill me-1"></i>Score Breakdown
                </h6>
                @php
                    $comp = $scoreComponents ?? [];
                    $breakdown = [
                        ['label'=>'Attendance',   'key'=>'attendance',   'weight'=>35, 'color'=>'#10b981'],
                        ['label'=>'Rating',       'key'=>'rating',       'weight'=>30, 'color'=>'#f59e0b'],
                        ['label'=>'Tasks',        'key'=>'task',         'weight'=>20, 'color'=>'#6366f1'],
                        ['label'=>'Consistency',  'key'=>'consistency',  'weight'=>15, 'color'=>'#0ea5e9'],
                    ];
                @endphp
                <div class="score-liquid-stack">
                @foreach($breakdown as $item)
                @php $val = round(($comp[$item['key']] ?? 0) * 100, 1); @endphp
                    <div class="score-liquid-item" style="--fill: {{ $val }}%; --accent: {{ $item['color'] }};">
                        <div class="score-liquid-fill"></div>
                        <div class="score-liquid-content">
                            <div>
                                <span class="score-liquid-label">{{ $item['label'] }}</span>
                                <small>{{ $item['weight'] }}% weight</small>
                            </div>
                            <strong>{{ $val }}%</strong>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- AI Suggestion --}}
    <div class="col-md-6">
        <div class="card h-100" style="border-left: 4px solid #8b5cf6;">
            <div class="card-body p-4">
                <h6 class="text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: #8b5cf6;"><i class="bi bi-robot"></i> Personalized AI Coach</h6>
                
                @if(isset($aiInsights) && count($aiInsights) > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($aiInsights as $insight)
                            <div class="d-flex align-items-start gap-3 p-3 rounded" style="background-color: {{ $insight['type'] === 'danger' ? '#fef2f2' : ($insight['type'] === 'warning' ? '#fffbeb' : ($insight['type'] === 'info' ? '#eff6ff' : '#f0fdf4')) }}; border-left: 3px solid {{ $insight['type'] === 'danger' ? '#ef4444' : ($insight['type'] === 'warning' ? '#f59e0b' : ($insight['type'] === 'info' ? '#3b82f6' : '#10b981')) }};">
                                <div class="fs-4 text-{{ $insight['type'] === 'danger' ? 'danger' : ($insight['type'] === 'warning' ? 'warning' : ($insight['type'] === 'info' ? 'primary' : 'success')) }}" style="margin-top: -4px;">
                                    <i class="bi {{ $insight['icon'] }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.88rem;">{{ $insight['title'] }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.4;">{{ $insight['message'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success py-2 px-3 mb-0" style="font-size:0.85rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> All metrics look great! Keep maintaining consistency.
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</div>
@endif

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card h-100">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($monthlyHours, 1) }}</div>
                    <div class="stat-label">Hours This Month</div>
                    <div class="stat-trend up"><i class="bi bi-activity"></i> Logged hours</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card h-100">
            <div class="stat-card">
                <div class="stat-icon success"><i class="bi bi-calendar2-check-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $leaveBalance }}</div>
                    <div class="stat-label">Leave Balance</div>
                    <div class="stat-trend {{ $leaveBalance > 5 ? 'up' : 'down' }}">
                        <i class="bi bi-{{ $leaveBalance > 5 ? 'check2' : 'exclamation' }}"></i>
                        {{ $leaveBalance > 5 ? 'Good balance' : 'Running low' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        {{-- Check In / Check Out card --}}
        <div class="checkin-card h-100">
            <div style="font-size:0.80rem;font-weight:700;color:#0f172a;margin-bottom:4px;">
                <i class="bi bi-clock me-1"></i> Attendance — {{ now()->format('d M Y') }}
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <form action="{{ route('employee.attendance.checkin') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 py-2 d-flex flex-column align-items-center" style="border-radius:14px;gap:4px;">
                            <i class="bi bi-box-arrow-in-right fs-5"></i>
                            <span style="font-size:0.78rem;font-weight:600;">Check In</span>
                        </button>
                    </form>
                </div>
                <div class="col-6">
                    <form action="{{ route('employee.attendance.checkout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 py-2 d-flex flex-column align-items-center" style="border-radius:14px;gap:4px;">
                            <i class="bi bi-box-arrow-left fs-5"></i>
                            <span style="font-size:0.78rem;font-weight:600;">Check Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Table + Quick Links --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <div class="stat-icon yellow d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <span>Recent Attendance</span>
                </div>
                <span class="badge bg-success">This month</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $record)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.84rem;">
                                        {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                                    </div>
                                    <div style="font-size:0.70rem;color:#64748b;">
                                        {{ \Carbon\Carbon::parse($record->date)->format('l') }}
                                    </div>
                                </td>
                                <td><span class="badge bg-success">{{ $record->check_in }}</span></td>
                                <td>
                                    @if($record->check_out)
                                        <span class="badge bg-secondary">{{ $record->check_out }}</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td style="font-weight:600;">
                                    {{ $record->total_hours ? number_format($record->total_hours, 1).' hrs' : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        <p>No attendance records yet.<br>Click <strong>Check In</strong> to start tracking.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <div class="card-title-group">
                    <div class="stat-icon purple d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <span>Quick Links</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column gap-2 p-3">
                <a href="{{ route('employee.leaves.create') }}" class="quick-action-item">
                    <div class="quick-action-icon" style="background:rgba(250,204,21,0.15);color:#d97706;">
                        <i class="bi bi-calendar-plus-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:0.84rem;">Apply for Leave</div>
                        <div style="font-size:0.72rem;color:#64748b;">Submit a leave request</div>
                    </div>
                </a>
                <a href="{{ route('employee.leaves.index') }}" class="quick-action-item">
                    <div class="quick-action-icon" style="background:rgba(245,158,11,0.13);color:#f59e0b;">
                        <i class="bi bi-calendar2-x-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:0.84rem;">My Leave History</div>
                        <div style="font-size:0.72rem;color:#64748b;">View all leave requests</div>
                    </div>
                </a>
                <a href="{{ route('employee.complaints.create') }}" class="quick-action-item">
                    <div class="quick-action-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;">
                        <i class="bi bi-exclamation-square-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:0.84rem;">Report an Issue</div>
                        <div style="font-size:0.72rem;color:#64748b;">Submit a grievance</div>
                    </div>
                </a>
                <a href="{{ route('employee.payrolls.index') }}" class="quick-action-item">
                    <div class="quick-action-icon" style="background:rgba(16,185,129,0.13);color:#10b981;">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:0.84rem;">View Payslips</div>
                        <div style="font-size:0.72rem;color:#64748b;">Download salary slips</div>
                    </div>
                </a>
                <a href="{{ route('employee.complaints.index') }}" class="quick-action-item">
                    <div class="quick-action-icon" style="background:rgba(99,102,241,0.12);color:#6366f1;">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:0.84rem;">My Complaints</div>
                        <div style="font-size:0.72rem;color:#64748b;">Track your grievances</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Bottom Area: Attendance Chart & Reward History --}}
<div class="row mt-4 mb-4 g-3 employee-dashboard-bottom">
    {{-- Reward History --}}
    <div class="col-lg-6">
        <div class="card employee-bottom-card">
            <div class="card-header bg-white">
                <div class="card-title-group">
                    <div class="stat-icon purple d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <span>My Reward History</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Rank</th>
                                <th>Level</th>
                                <th>Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rewardHistory as $reward)
                            <tr>
                                <td><span class="fw-bold">{{ DateTime::createFromFormat('Y-m', $reward->month)->format('M Y') }}</span></td>
                                <td>#{{ $reward->rank }}</td>
                                <td>
                                    <span class="badge bg-{{ $reward->reward_tier === 'Level 5' ? 'warning' : ($reward->reward_tier === 'Level 4' ? 'primary' : 'secondary') }}">
                                        {{ $reward->reward_tier }}
                                    </span>
                                </td>
                                <td class="text-success fw-bold">+{{ $reward->bonus_points_awarded }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state p-4 text-center">
                                        <i class="bi bi-star text-muted" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No rewards yet. Keep it up!</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Chart (Moved to Base as Requested) --}}
    <div class="col-lg-6">
        <div class="card employee-bottom-card">
            <div class="card-header bg-white">
                <div class="card-title-group">
                    <div class="stat-icon yellow d-flex" style="width:32px;height:32px;border-radius:9px;font-size:0.9rem;">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <span>Weekly Hours Logged</span>
                </div>
            </div>
            <div class="card-body">
                <div class="employee-hours-chart">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartDates ?? []) !!},
                datasets: [{
                    label: 'Hours Logged',
                    data: {!! json_encode($chartHours ?? []) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 12,
                        ticks: { stepSize: 2 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
