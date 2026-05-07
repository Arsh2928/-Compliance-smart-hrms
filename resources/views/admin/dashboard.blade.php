@extends('layouts.app')
@section('title', auth()->user()->role === 'hr' ? 'HR Dashboard' : 'Admin Dashboard')

@section('content')
@php $role = auth()->user()->role; @endphp

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body p-4 p-lg-5 d-flex justify-content-between align-items-start align-items-lg-center flex-column flex-lg-row gap-3">
        <div>
          <h4 class="font-weight-bolder mb-1">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
            {{ explode(' ', auth()->user()->name)[0] }}
          </h4>
          <p class="text-sm text-secondary mb-0">Here is what is happening today - {{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($role === 'hr' && isset($hrEmployee))
                <form action="{{ route('hr.attendance.checkin') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-success mb-0 shadow-sm"><i class="bi bi-box-arrow-in-right me-1"></i> Check In</button>
                </form>
                <form action="{{ route('hr.attendance.checkout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-outline-danger mb-0 shadow-sm"><i class="bi bi-box-arrow-left me-1"></i> Check Out</button>
                </form>
            @endif
            <a href="{{ $role === 'hr' ? route('hr.employees.create') : route('admin.employees.create') }}" class="btn bg-gradient-warning mb-0">
              <i class="bi bi-person-plus-fill me-2"></i>Add Employee
            </a>
        </div>
      </div>
    </div>
  </div>

  @if($role === 'hr' && isset($hrEmployee))
  <div class="col-12 mb-4">
    <div class="card">
        <div class="card-header pb-0">
            <h6>My Attendance Log (Last 7 Days)</h6>
        </div>
        <div class="card-body p-3">
            <div class="chart">
                <canvas id="hrAttendanceChart" class="chart-canvas" height="150"></canvas>
            </div>
        </div>
    </div>
  </div>
  @endif

  <div class="col-12">
    <h6 class="text-uppercase text-xs font-weight-bolder opacity-7 mb-3">Top Performers This Month</h6>
  </div>
  @forelse($topPerformers ?? [] as $index => $performer)
    @php
      $badges  = is_array($performer->badges) ? $performer->badges : [];
      $highest = !empty($badges) ? last($badges) : '';
      $medal   = $highest === 'Gold' ? 'bi-award-fill' : ($highest === 'Silver' ? 'bi-award' : ($highest === 'Bronze' ? 'bi-award' : 'bi-rocket-takeoff'));
      $grad    = $index === 0 ? 'bg-gradient-warning' : ($index === 1 ? 'bg-gradient-secondary' : 'bg-gradient-primary');
    @endphp
    <div class="col-md-6 col-xl-4 mb-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-lg {{ $grad }} shadow d-flex align-items-center justify-content-center">
            <span class="text-white font-weight-bolder">{{ substr($performer->user->name ?? 'A', 0, 1) }}</span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $performer->user->name ?? 'Unknown' }}</h6>
            <p class="text-sm text-secondary mb-0">Score: <span class="font-weight-bolder text-warning">{{ $performer->performance_score ?? 0 }}</span></p>
          </div>
          <div class="text-end">
            <i class="bi {{ $medal }} fs-4 text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-body text-center text-secondary">
          No top performers ranked yet. Monthly evaluation pending.
        </div>
      </div>
    </div>
  @endforelse

  <div class="col-12">
    <h6 class="text-uppercase text-xs font-weight-bolder text-danger opacity-8 mb-3">Needs Attention</h6>
  </div>
  @forelse($lowPerformers ?? [] as $performer)
    <div class="col-md-6 col-xl-4 mb-4">
      <div class="card h-100 border border-danger border-opacity-25">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-lg bg-gradient-danger shadow d-flex align-items-center justify-content-center">
            <span class="text-white font-weight-bolder">{{ substr($performer->user->name ?? 'A', 0, 1) }}</span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $performer->user->name ?? 'Unknown' }}</h6>
            <p class="text-sm mb-0 text-danger">Score: <span class="font-weight-bolder">{{ $performer->performance_score ?? 0 }}</span></p>
          </div>
          <a href="{{ auth()->user()->role === 'hr' ? route('hr.employees.show', $performer->id) : route('admin.employees.show', $performer->id) }}" class="btn btn-sm btn-outline-danger mb-0">
            Review
          </a>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-body text-center text-secondary">
          <i class="bi bi-check-circle me-1 text-success"></i>All employees are performing well.
        </div>
      </div>
    </div>
  @endforelse

  <div class="col-12">
    <h6 class="text-uppercase text-xs font-weight-bolder opacity-7 mb-3">AI Coach Predictions</h6>
  </div>
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-xs text-uppercase font-weight-bolder text-primary opacity-8 mb-3">Projected Monthly Winner</p>
        @if($topPredictor)
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg bg-gradient-primary shadow d-flex align-items-center justify-content-center">
              <span class="text-white font-weight-bolder">{{ substr($topPredictor->user->name ?? 'A', 0, 1) }}</span>
            </div>
            <div>
              <h6 class="mb-1">{{ $topPredictor->user->name }}</h6>
              <p class="text-sm text-secondary mb-0">Based on highest live point velocity (+{{ $topPredictor->points }} pts).</p>
            </div>
          </div>
        @else
          <p class="text-secondary mb-0">Not enough data to predict a winner yet.</p>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-xs text-uppercase font-weight-bolder text-danger opacity-8 mb-3">High Burnout Risk</p>
        @if($burnoutRisks->count() > 0)
          <div class="d-flex flex-column gap-2">
            @foreach($burnoutRisks as $risk)
              <div class="d-flex justify-content-between align-items-center p-2 border-radius-md ui-soft-danger">
                <div class="font-weight-bolder">{{ $risk->user->name }}</div>
                <span class="badge bg-gradient-danger">Logged {{ $risk->burnout_hours }} hrs/week</span>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-success mb-0"><i class="bi bi-check-circle-fill me-1"></i>No employees are at risk of burnout.</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-sm-6 mb-4">
    <div class="card card-stats">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="numbers">
              <p class="mb-0">Total Employees</p>
              <h4 class="font-weight-bolder">{{ $totalEmployees }}</h4>
            </div>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
              <i class="bi bi-people-fill text-white"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-4">
    <div class="card card-stats">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="numbers">
              <p class="mb-0">Present Today</p>
              <h4 class="font-weight-bolder">{{ $attendanceToday }}</h4>
            </div>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
              <i class="bi bi-calendar-check-fill text-white"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-4">
    <div class="card card-stats">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="numbers">
              <p class="mb-0">Pending Leaves</p>
              <h4 class="font-weight-bolder">{{ $pendingLeaves }}</h4>
            </div>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
              <i class="bi bi-hourglass-split text-white"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-4">
    <div class="card card-stats">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="numbers">
              <p class="mb-0">Open Complaints</p>
              <h4 class="font-weight-bolder">{{ $openComplaints }}</h4>
            </div>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
              <i class="bi bi-exclamation-octagon-fill text-white"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card h-100">
      <div class="card-header pb-0">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body pt-3 d-flex flex-column gap-2">
        <a href="{{ $role === 'hr' ? route('hr.employees.create') : route('admin.employees.create') }}" class="quick-action-item">
          <div class="quick-action-icon bg-gradient-warning text-white"><i class="bi bi-person-plus-fill"></i></div>
          <div>
            <div class="text-sm font-weight-bolder">Add New Employee</div>
            <div class="text-xs text-secondary">Onboard a team member</div>
          </div>
        </a>
        <a href="{{ $role === 'hr' ? route('hr.leaves.index') : route('admin.leaves.index') }}" class="quick-action-item">
          <div class="quick-action-icon bg-gradient-info text-white"><i class="bi bi-calendar2-x"></i></div>
          <div>
            <div class="text-sm font-weight-bolder">Review Leaves</div>
            <div class="text-xs text-secondary">Approve or reject requests</div>
          </div>
        </a>
        <a href="{{ $role === 'hr' ? route('hr.payrolls.create') : route('admin.payrolls.create') }}" class="quick-action-item">
          <div class="quick-action-icon bg-gradient-success text-white"><i class="bi bi-cash-coin"></i></div>
          <div>
            <div class="text-sm font-weight-bolder">Generate Payroll</div>
            <div class="text-xs text-secondary">Process monthly salaries</div>
          </div>
        </a>
        <a href="{{ $role === 'hr' ? route('hr.contracts.index') : route('admin.contracts.index') }}" class="quick-action-item">
          <div class="quick-action-icon bg-gradient-primary text-white"><i class="bi bi-file-earmark-text"></i></div>
          <div>
            <div class="text-sm font-weight-bolder">View Contracts</div>
            <div class="text-xs text-secondary">Manage employee contracts</div>
          </div>
        </a>
        <a href="{{ $role === 'hr' ? route('hr.complaints.index') : route('admin.complaints.index') }}" class="quick-action-item">
          <div class="quick-action-icon bg-gradient-danger text-white"><i class="bi bi-exclamation-octagon"></i></div>
          <div>
            <div class="text-sm font-weight-bolder">Open Complaints</div>
            <div class="text-xs text-secondary">Resolve grievances</div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-8 mb-4">
    <div class="card h-100">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Compliance Overview</h6>
        <span class="text-xs text-secondary">Updated just now</span>
      </div>
      <div class="card-body pt-3">
        @php
          $attendancePct = $totalEmployees > 0 ? round(($attendanceToday / $totalEmployees) * 100) : 0;
          
          $totalLeavesCount = \App\Models\Leave::count();
          $leavePct = $totalLeavesCount > 0 ? round((($totalLeavesCount - $pendingLeaves) / $totalLeavesCount) * 100) : null;
          
          $totalComplaintsCount = \App\Models\Complaint::count();
          $complaintsPct = $totalComplaintsCount > 0 ? round((($totalComplaintsCount - $openComplaints) / $totalComplaintsCount) * 100) : null;
          
          $validMetrics = [$attendancePct];
          if ($leavePct !== null) $validMetrics[] = $leavePct;
          if ($complaintsPct !== null) $validMetrics[] = $complaintsPct;
          
          $overallPct = count($validMetrics) > 0 ? round(array_sum($validMetrics) / count($validMetrics)) : 0;

          // Helper to determine tone based on percentage
          $getTone = function($pct, $type) {
              if ($pct === null) return ['tone' => 'secondary', 'accent' => '#94a3b8'];
              if ($pct >= 85) return ['tone' => 'success', 'accent' => '#22c55e'];
              if ($pct >= 60) return ['tone' => 'warning', 'accent' => '#eab308'];
              return ['tone' => 'danger', 'accent' => '#ef4444'];
          };

          $attStyle = $getTone($attendancePct, 'attendance');
          $leaveStyle = $getTone($leavePct, 'leave');
          $compStyle = $getTone($complaintsPct, 'complaints');
          $overallStyle = $getTone($overallPct, 'overall');

          $complianceMetrics = [
            ['label' => 'Attendance Rate', 'value' => $attendancePct.'%', 'fill' => $attendancePct, 'tone' => $attStyle['tone'], 'accent' => $attStyle['accent'], 'icon' => 'bi-calendar-check-fill'],
            ['label' => 'Leave Resolution', 'value' => $leavePct !== null ? $leavePct.'%' : 'N/A', 'fill' => $leavePct ?? 0, 'tone' => $leaveStyle['tone'], 'accent' => $leaveStyle['accent'], 'icon' => 'bi-calendar2-x-fill'],
            ['label' => 'Complaint Resolution', 'value' => $complaintsPct !== null ? $complaintsPct.'%' : 'N/A', 'fill' => $complaintsPct ?? 0, 'tone' => $compStyle['tone'], 'accent' => $compStyle['accent'], 'icon' => 'bi-exclamation-octagon-fill'],
            ['label' => 'Overall Health', 'value' => $overallPct.'%', 'fill' => $overallPct, 'tone' => $overallStyle['tone'], 'accent' => $overallStyle['accent'], 'icon' => 'bi-shield-check'],
          ];
        @endphp
        <div class="compliance-stack">
          @foreach($complianceMetrics as $metric)
          <div class="compliance-box compliance-box-{{ $metric['tone'] }}" style="--fill: {{ $metric['fill'] }}%; --accent: {{ $metric['accent'] }};">
            <div class="compliance-fill"></div>
            <div class="compliance-content">
              <div class="compliance-icon">
                <i class="bi {{ $metric['icon'] }}"></i>
              </div>
              <div class="compliance-copy">
                <span class="compliance-label">{{ $metric['label'] }}</span>
                <span class="compliance-caption">{{ $metric['fill'] }}% target coverage</span>
              </div>
              <span class="compliance-value">{{ $metric['value'] }}</span>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Charts moved to the bottom --}}
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Weekly Attendance Trends</h6>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Live</span>
      </div>
      <div class="card-body">
        <div class="ui-chart ui-chart-lg">
          <canvas id="attendanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header pb-0">
        <h6 class="mb-0">Performance Trends</h6>
      </div>
      <div class="card-body">
        <div class="ui-chart ui-chart-md">
          <canvas id="performanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header pb-0">
        <h6 class="mb-0">Reward Distribution</h6>
      </div>
      <div class="card-body">
        <div class="ui-chart ui-chart-md">
          <canvas id="rewardChart"></canvas>
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
    gradGreen.addColorStop(0, 'rgba(34,197,94,0.26)');
    gradGreen.addColorStop(1, 'rgba(34,197,94,0.0)');

    const gradOrange = canvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradOrange.addColorStop(0, 'rgba(79,70,229,0.18)');
    gradOrange.addColorStop(1, 'rgba(79,70,229,0.0)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDates) !!},
            datasets: [
                {
                    label: 'Employees Present',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: gradGreen,
                    borderColor: '#22c55e',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#22c55e',
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
                    borderColor: '#4f46e5',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: '#4f46e5',
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
            maintainAspectRatio: false,
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
                    titleColor: '#c7d2fe',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(79,70,229,0.28)',
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

const perfCtx = document.getElementById('performanceChart');
if (perfCtx) {
    const canvas = perfCtx;
    const gradPurple = canvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradPurple.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
    gradPurple.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: {!! json_encode($performanceLabels ?? []) !!},
            datasets: [{
                label: 'Average Score',
                data: {!! json_encode($performanceData ?? []) !!},
                backgroundColor: gradPurple,
                borderColor: '#4f46e5',
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { color: 'rgba(241,245,249,0.8)' },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

const rewardCtx = document.getElementById('rewardChart');
if (rewardCtx) {
    new Chart(rewardCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($rewardLabels ?? []) !!},
            datasets: [{
                data: {!! json_encode($rewardData ?? []) !!},
                backgroundColor: ['#eab308', '#94a3b8', '#d97706', '#9ca3af'], // Gold, Silver, Bronze, None
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: '#64748b', font: { size: 10 } }
                }
            },
            cutout: '70%'
        }
    });
}

@if($role === 'hr' && isset($hrChartDates))
var ctxHr = document.getElementById("hrAttendanceChart");
if(ctxHr) {
    new Chart(ctxHr, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hrChartDates ?? []) !!},
            datasets: [{
                label: 'Hours Logged',
                data: {!! json_encode($hrChartHours ?? []) !!},
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
                y: { beginAtZero: true, max: 12, ticks: { stepSize: 2 } }
            },
            plugins: { legend: { display: false } }
        }
    });
}
@endif
</script>
@endpush
