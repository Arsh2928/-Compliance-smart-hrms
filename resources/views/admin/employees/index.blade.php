@extends('layouts.app')
@section('title', 'Employee Management')
@php $authRole = auth()->user()->role; @endphp

@section('content')
<div class="page-header">
    <h4><i class="bi bi-people-fill me-2 text-primary"></i>Employee Management</h4>
    @if(in_array($authRole, ['admin', 'hr']))
    <a href="{{ $authRole === 'hr' ? route('hr.employees.create') : route('admin.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Add Employee
    </a>
    @endif
</div>

@if($pendingUsers->count() > 0)
<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark fw-bold">
        <i class="bi bi-clock-history me-1"></i> Pending Approvals ({{ $pendingUsers->count() }})
    </div>
    <div class="table-responsive">
        <table class="table mb-0 pending-approval-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingUsers as $pUser)
                <tr>
                    <td>{{ $pUser->name }}</td>
                    <td>{{ $pUser->email }}</td>
                    <td>{{ $pUser->created_at->format('d M Y H:i') }}</td>
                    <td class="pending-approval-actions-cell">
                        @php $r = $authRole; @endphp
                        <div class="pending-approval-actions">
                            <form action="{{ $r === 'hr' ? route('hr.employees.approve', $pUser->id) : route('admin.employees.approve', $pUser->id) }}" method="POST" class="pending-approval-form">
                                @csrf
                                <select name="department_id" class="form-select form-select-sm" required>
                                    <option value="">Select Dept...</option>
                                    @foreach(\App\Models\Department::all() as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>
                            </form>
                            <form action="{{ $r === 'hr' ? route('hr.employees.reject', $pUser->id) : route('admin.employees.reject', $pUser->id) }}" method="POST"
                                  onsubmit="return confirm('Reject and delete {{ $pUser->name }}\'s registration?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-lg me-1"></i>Reject
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body pb-0">
        {{-- Search / Filter --}}
        <form action="{{ $authRole === 'hr' ? route('hr.employees.index') : route('admin.employees.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Search name, email, code…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::all() as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ $authRole === 'hr' ? route('hr.employees.index') : route('admin.employees.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table employee-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td><code class="text-primary fw-bold">{{ $employee->employee_code }}</code></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="topbar-avatar" style="width:32px;height:32px;font-size:0.75rem;">
                                {{ strtoupper(substr($employee->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:0.87rem;">{{ $employee->user->name ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ $employee->user->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $employee->department->name ?? '—' }}</td>
                    <td>{{ $employee->user->phone ?? '—' }}</td>
                    <td>
                        @php $employeeRole = $employee->user->role ?? 'employee'; @endphp
                        <span class="badge bg-{{ $employeeRole === 'admin' ? 'danger' : ($employeeRole === 'hr' ? 'warning' : 'success') }}">
                            {{ ucfirst($employeeRole) }}
                        </span>
                    </td>
                    <td class="employee-actions-cell">
                        <div class="table-row-actions">
                            <a href="{{ $authRole === 'hr' ? route('hr.employees.show', $employee) : route('admin.employees.show', $employee) }}"
                               class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @if(in_array($authRole, ['admin', 'hr']))
                            @if((string) auth()->id() !== (string) $employee->user_id)
                            <button type="button"
                                    class="btn btn-sm btn-outline-warning employee-rate-btn"
                                    title="Rate"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rateEmployeeModal"
                                    data-employee-name="{{ $employee->user->name ?? 'Employee' }}"
                                    data-rate-url="{{ $authRole === 'hr' ? route('hr.employees.rate', $employee->id) : route('admin.employees.rate', $employee->id) }}">
                                <i class="bi bi-star-fill"></i>
                            </button>
                            @endif
                            <a href="{{ $authRole === 'hr' ? route('hr.employees.edit', $employee) : route('admin.employees.edit', $employee) }}"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @endif
                            @if($authRole === 'admin')
                            <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST"
                                  onsubmit="return confirm('Delete this employee permanently?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            @if(auth()->user()->role === 'admin')
                            <p>No employees found. <a href="{{ route('admin.employees.create') }}">Add one now</a>.</p>
                            @else
                            <p>No employees found.</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
    <div class="card-body pt-2">
        {{ $employees->withQueryString()->links() }}
    </div>
    @endif
</div>

@if(in_array($authRole, ['admin', 'hr']))
<div class="modal fade" id="rateEmployeeModal" tabindex="-1" aria-labelledby="rateEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rate-modal">
            <form method="POST" action="" id="rateEmployeeForm">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="rateEmployeeModalLabel">
                            <i class="bi bi-star-fill me-2 text-warning"></i>Rate Employee
                        </h5>
                        <p class="text-secondary mb-0 rate-modal-subtitle" id="rateEmployeeName">Select an employee to rate</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        $ratingCategories = [
                            'work_quality' => ['label' => 'Work Quality', 'icon' => 'bi-briefcase-fill'],
                            'punctuality' => ['label' => 'Punctuality', 'icon' => 'bi-clock-fill'],
                            'teamwork' => ['label' => 'Teamwork', 'icon' => 'bi-people-fill'],
                            'task_completion' => ['label' => 'Task Completion', 'icon' => 'bi-check2-circle'],
                            'discipline' => ['label' => 'Discipline', 'icon' => 'bi-shield-check'],
                        ];
                    @endphp

                    <div class="rating-grid">
                        @foreach($ratingCategories as $key => $meta)
                        <div class="rating-control">
                            <div class="rating-control-header">
                                <span><i class="bi {{ $meta['icon'] }}"></i>{{ $meta['label'] }}</span>
                                <strong data-rating-value="{{ $key }}">3 / 5</strong>
                            </div>
                            <input type="range"
                                   class="form-range rating-slider"
                                   name="categories[{{ $key }}]"
                                   min="1"
                                   max="5"
                                   step="0.5"
                                   value="3"
                                   data-rating-slider="{{ $key }}">
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Feedback <span class="text-secondary fw-normal">(optional)</span></label>
                        <textarea name="feedback" class="form-control" rows="4" maxlength="1000" placeholder="Add observations, strengths, or improvement notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-2"></i>Submit Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rateEmployeeModal');
    const form = document.getElementById('rateEmployeeForm');
    const nameEl = document.getElementById('rateEmployeeName');

    document.querySelectorAll('.employee-rate-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (form) {
                form.action = button.dataset.rateUrl;
                form.reset();
            }
            if (nameEl) {
                nameEl.textContent = button.dataset.employeeName;
            }
            document.querySelectorAll('[data-rating-value]').forEach(function (valueEl) {
                valueEl.textContent = '3 / 5';
            });
        });
    });

    modal?.querySelectorAll('[data-rating-slider]').forEach(function (slider) {
        slider.addEventListener('input', function () {
            const valueEl = modal.querySelector('[data-rating-value="' + slider.dataset.ratingSlider + '"]');
            if (valueEl) {
                valueEl.textContent = slider.value + ' / 5';
            }
        });
    });
});
</script>
@endpush
