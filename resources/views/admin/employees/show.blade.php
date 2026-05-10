@extends('layouts.app')
@section('title', 'Employee Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4><i class="bi bi-person-lines-fill me-2 text-primary"></i>Employee Details: {{ $employee->user->name ?? 'Unknown' }}</h4>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">
    {{-- Left Column: Profile & Info --}}
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="topbar-avatar mx-auto mb-3" style="width:80px;height:80px;font-size:2rem;background:linear-gradient(135deg, #0f172a, #3b82f6);color:#fff;">
                    {{ strtoupper(substr($employee->user->name ?? 'U', 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $employee->user->name }}</h5>
                <p class="text-muted mb-2">{{ $employee->employee_code }} &bull; {{ $employee->department->name ?? 'No Department' }}</p>
                <span class="badge bg-{{ $employee->user->role === 'admin' ? 'danger' : ($employee->user->role === 'hr' ? 'warning' : 'success') }}">
                    {{ ucfirst($employee->user->role ?? 'Employee') }}
                </span>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between px-3">
                    <span class="text-muted">Email</span>
                    <strong>{{ $employee->user->email }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-3">
                    <span class="text-muted">Phone</span>
                    <strong>{{ $employee->user->phone ?? '—' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-3">
                    <span class="text-muted">Joined</span>
                    <strong>{{ \Carbon\Carbon::parse($employee->joined_date)->format('d M Y') }}</strong>
                </li>
            </ul>
        </div>
    </div>

    {{-- Right Column: Tabs for all data --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <ul class="nav nav-tabs border-bottom-0" id="employeeTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="att-tab" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link" id="perf-tab" data-bs-toggle="tab" href="#performance">Performance</a></li>
                    <li class="nav-item"><a class="nav-link" id="rate-tab" data-bs-toggle="tab" href="#ratings">Ratings</a></li>
                    <li class="nav-item"><a class="nav-link" id="leave-tab" data-bs-toggle="tab" href="#leaves">Leaves</a></li>
                    <li class="nav-item"><a class="nav-link" id="pay-tab" data-bs-toggle="tab" href="#payroll">Payroll</a></li>
                    <li class="nav-item"><a class="nav-link" id="comp-tab" data-bs-toggle="tab" href="#complaints">Complaints</a></li>
                </ul>
            </div>
            <div class="card-body border-top">
                <div class="tab-content" id="employeeTabsContent">
                    {{-- Attendance Tab --}}
                    <div class="tab-pane fade show active" id="attendance">
                        <h6>Recent Attendance (Last 10)</h6>
                        <table class="table table-sm mt-3">
                            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th></tr></thead>
                            <tbody>
                                @forelse($employee->attendances->sortByDesc('date')->take(10) as $att)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                                    <td>{{ $att->check_in }}</td>
                                    <td>{{ $att->check_out ?? 'Pending' }}</td>
                                    <td>{{ $att->total_hours }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted">No attendance records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Performance Tab --}}
                    <div class="tab-pane fade" id="performance">
                        <h6>Monthly Performance Records</h6>
                        <table class="table table-sm mt-3">
                            <thead><tr><th>Month</th><th>Final Score</th><th>Rank</th><th>Tier</th></tr></thead>
                            <tbody>
                                @forelse($employee->performanceRecords->sortByDesc('month') as $perf)
                                <tr>
                                    <td>{{ $perf->month }}</td>
                                    <td>{{ round($perf->final_score ?? $perf->live_score ?? 0, 1) }}</td>
                                    <td>#{{ $perf->rank ?? '—' }}</td>
                                    <td>{{ $perf->reward_tier ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted">No performance records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Ratings Tab --}}
                    <div class="tab-pane fade" id="ratings">
                        @php $rRole = auth()->user()->role; @endphp

                        {{-- Flash messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Rate This Employee Form (Admin/HR only) --}}
                        @if(in_array($rRole, ['admin', 'hr']) && (string)auth()->user()->id !== (string)$employee->user_id)
                        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc, #eff6ff); border-radius: 14px;">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-1" style="color: #1e3a5f;">
                                    <i class="bi bi-star-fill text-warning me-2"></i>Rate This Employee
                                </h6>
                                <p class="text-muted small mb-3">Submit a performance rating for {{ $employee->user->name ?? 'this employee' }}. You can rate once every 7 days.</p>

                                <form method="POST" action="{{ $rRole === 'hr' ? route('hr.employees.rate', $employee->id) : route('admin.employees.rate', $employee->id) }}">
                                    @csrf

                                    {{-- 5-Category Star Rating --}}
                                    <div class="row g-3 mb-3">
                                        @php
                                        $ratingCategories = [
                                            'work_quality'    => ['label' => 'Work Quality',    'icon' => 'bi-briefcase-fill',       'color' => '#8b5cf6'],
                                            'punctuality'     => ['label' => 'Punctuality',     'icon' => 'bi-clock-fill',           'color' => '#3b82f6'],
                                            'teamwork'        => ['label' => 'Teamwork',        'icon' => 'bi-people-fill',          'color' => '#10b981'],
                                            'task_completion' => ['label' => 'Task Completion', 'icon' => 'bi-check2-circle',        'color' => '#f59e0b'],
                                            'discipline'      => ['label' => 'Discipline',      'icon' => 'bi-shield-check',         'color' => '#ef4444'],
                                        ];
                                        @endphp

                                        @foreach($ratingCategories as $key => $meta)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold d-flex align-items-center gap-2" style="font-size: 0.85rem;">
                                                <i class="bi {{ $meta['icon'] }}" style="color: {{ $meta['color'] }};"></i>
                                                {{ $meta['label'] }}
                                                <span class="ms-auto fw-bold" id="val-{{ $key }}" style="color: {{ $meta['color'] }};">3 / 5</span>
                                            </label>
                                            <div class="star-rating-row d-flex align-items-center gap-2">
                                                <span class="text-muted small">1</span>
                                                <input type="range" class="form-range star-slider flex-grow-1"
                                                       name="categories[{{ $key }}]"
                                                       id="slider-{{ $key }}"
                                                       min="1" max="5" step="0.5" value="3"
                                                       oninput="document.getElementById('val-{{ $key }}').textContent = this.value + ' / 5'"
                                                       style="accent-color: {{ $meta['color'] }};">
                                                <span class="text-muted small">5</span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- Feedback --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">
                                            <i class="bi bi-chat-left-text-fill text-primary me-1"></i> Feedback / Comments
                                            <span class="text-muted fw-normal">(optional)</span>
                                        </label>
                                        <textarea name="feedback" class="form-control" rows="3"
                                                  placeholder="Share specific observations, achievements, or areas for improvement..."
                                                  style="font-size: 0.9rem; resize: vertical; min-height: 80px;"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-warning fw-bold px-4">
                                        <i class="bi bi-send-fill me-2"></i>Submit Rating
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif

                        {{-- Rating History --}}
                        <h6 class="fw-bold mb-2 text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="bi bi-clock-history me-1"></i> Rating History
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Avg Score</th>
                                        <th>Work Quality</th>
                                        <th>Punctuality</th>
                                        <th>Teamwork</th>
                                        <th>Task Done</th>
                                        <th>Discipline</th>
                                        <th>Feedback</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->ratings->sortByDesc('month') as $rate)
                                    @php $cats = is_array($rate->categories) ? $rate->categories : []; @endphp
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $rate->month }}</span></td>
                                        <td>
                                            <span class="fw-bold" style="color: {{ ($rate->average_rating ?? 0) >= 4 ? '#10b981' : (($rate->average_rating ?? 0) >= 2.5 ? '#f59e0b' : '#ef4444') }};">
                                                {{ number_format($rate->average_rating ?? 0, 1) }} / 5
                                            </span>
                                        </td>
                                        <td>{{ number_format($cats['work_quality'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($cats['punctuality'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($cats['teamwork'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($cats['task_completion'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($cats['discipline'] ?? 0, 1) }}</td>
                                        <td>
                                            @if($rate->feedback)
                                                <span title="{{ $rate->feedback }}" style="cursor:help;">
                                                    {{ Str::limit($rate->feedback, 30) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            @if(isset($cats['_suspicious']) && $cats['_suspicious'])
                                                <i class="bi bi-exclamation-triangle-fill text-warning" title="Flagged as suspicious"></i>
                                            @endif
                                            {{ $rate->evaluator->name ?? 'Anonymous' }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">
                                            <i class="bi bi-star me-1"></i> No ratings submitted yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Leaves Tab --}}
                    <div class="tab-pane fade" id="leaves">
                        <h6>Leave Requests</h6>
                        <table class="table table-sm mt-3">
                            <thead><tr><th>Dates</th><th>Type</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($employee->leaves->sortByDesc('created_at') as $leave)
                                <tr>
                                    <td>{{ $leave->start_date }} to {{ $leave->end_date }}</td>
                                    <td>{{ ucfirst($leave->type ?? $leave->leave_type ?? '—') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-muted">No leave requests.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Payroll Tab --}}
                    <div class="tab-pane fade" id="payroll">
                        <h6>Payroll History</h6>
                        <table class="table table-sm mt-3">
                            <thead><tr><th>Month</th><th>Net Salary</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($employee->payrolls->sortByDesc('month') as $pay)
                                <tr>
                                    <td>{{ $pay->month }}</td>
                                    <td>₹{{ number_format($pay->net_salary, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $pay->status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($pay->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-muted">No payroll records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Complaints Tab --}}
                    <div class="tab-pane fade" id="complaints">
                        <h6>Grievances/Complaints</h6>
                        <table class="table table-sm mt-3">
                            <thead><tr><th>Title</th><th>Status</th><th>Submitted On</th></tr></thead>
                            <tbody>
                                @forelse($employee->user->complaints->sortByDesc('created_at') as $comp)
                                <tr>
                                    <td>{{ $comp->title }}</td>
                                    <td>
                                        <span class="badge bg-{{ $comp->status === 'resolved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($comp->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $comp->created_at->format('d M Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-muted">No complaints filed.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
