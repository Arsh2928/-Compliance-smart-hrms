<div class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-shield-check me-2 text-primary"></i>
        <span>ComplianceSys</span>
    </div>
    
    <div class="sidebar-nav">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Admin Dashboard
            </a>
            <a href="{{ route('admin.employees.index') }}" class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Employees
            </a>
            <a href="{{ route('admin.leaves.index') }}" class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-x"></i> Leave Requests
            </a>
            <a href="{{ route('admin.complaints.index') }}" class="nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i> Complaints
            </a>
            <a href="{{ route('admin.payrolls.index') }}" class="nav-link {{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Payroll
            </a>
            <a href="{{ route('admin.contracts.index') }}" class="nav-link {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Contracts
            </a>
        @elseif(auth()->user()->role === 'hr')
            <a href="{{ route('hr.dashboard') }}" class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> HR Dashboard
            </a>
            <a href="{{ route('admin.employees.index') }}" class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Employees
            </a>
            <a href="{{ route('admin.leaves.index') }}" class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-x"></i> Leave Approvals
            </a>
        @elseif(auth()->user()->role === 'employee')
            <a href="{{ route('employee.dashboard') }}" class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> My Dashboard
            </a>
            <a href="{{ route('employee.leaves.index') }}" class="nav-link {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-x"></i> My Leaves
            </a>
            <a href="{{ route('employee.complaints.index') }}" class="nav-link {{ request()->routeIs('employee.complaints.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i> My Complaints
            </a>
            <a href="{{ route('employee.payrolls.index') }}" class="nav-link {{ request()->routeIs('employee.payrolls.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> My Payslips
            </a>
        @endif
    </div>
</div>
