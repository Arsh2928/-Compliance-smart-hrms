@extends('layouts.app')
@section('title', 'Employee Management')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-people-fill me-2 text-primary"></i>Employee Management</h4>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Add Employee
    </a>
    @endif
</div>

<div class="card">
    <div class="card-body pb-0">
        {{-- Search / Filter --}}
        <form action="{{ route('admin.employees.index') }}" method="GET" class="row g-2 mb-3">
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
                <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
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
                    <td>{{ $employee->phone ?? '—' }}</td>
                    <td>
                        @php $r = $employee->user->role ?? 'employee'; @endphp
                        <span class="badge bg-{{ $r === 'admin' ? 'danger' : ($r === 'hr' ? 'warning' : 'success') }}">
                            {{ ucfirst($r) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.employees.show', $employee) }}"
                               class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.employees.edit', $employee) }}"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
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
@endsection
