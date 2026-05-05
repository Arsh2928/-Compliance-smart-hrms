{{-- Argon Dashboard 3 sidebar (Bootstrap 5) --}}
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white shadow-xl" id="sidenav-main">
  <div class="sidenav-header">
    <i class="p-3 cursor-pointer text-dark opacity-4 position-absolute end-0 top-0 d-none d-xl-none" id="iconSidenav"></i>
    <a class="navbar-brand m-0 py-0 d-flex align-items-center" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
      <div class="d-flex align-items-center justify-content-center rounded-3 me-2 ui-sidenav-brand-icon">
        <i class="bi bi-shield-check text-primary"></i>
      </div>
      <span class="font-weight-bold text-dark text-sm">ComplianceSys</span>
    </a>
  </div>

  <hr class="horizontal dark mt-0 mb-2">

  <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      @auth
        @php
          $role = auth()->user()->role;

          function sideLink($label, $icon, $href, $active) {
            $cls = $active ? 'active shadow' : '';
            return '<li class="nav-item"><a class="nav-link '.$cls.'" href="'.$href.'">
              <div class="text-center me-2 d-flex align-items-center justify-content-center ui-sidenav-icon">
                <i class="bi '.$icon.'"></i>
              </div>
              <span class="nav-link-text ms-1">'.$label.'</span></a></li>';
          }
        @endphp

        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Quick</h6>
        </li>
        {!! sideLink('Home','bi-house-door-fill',route('home'),request()->routeIs('home')) !!}

        @if($role==='admin')
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Management</h6>
          </li>
          {!! sideLink('Dashboard','bi-grid-fill',route('admin.dashboard'),request()->routeIs('admin.dashboard')) !!}
          {!! sideLink('Employees','bi-people-fill',route('admin.employees.index'),request()->routeIs('admin.employees.*')) !!}
          {!! sideLink('Leave Requests','bi-calendar2-x-fill',route('admin.leaves.index'),request()->routeIs('admin.leaves.*')) !!}
          {!! sideLink('Complaints','bi-exclamation-octagon-fill',route('admin.complaints.index'),request()->routeIs('admin.complaints.*')) !!}
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Finance</h6>
          </li>
          {!! sideLink('Payroll','bi-cash-coin',route('admin.payrolls.index'),request()->routeIs('admin.payrolls.*')) !!}
          {!! sideLink('Contracts','bi-file-earmark-text-fill',route('admin.contracts.index'),request()->routeIs('admin.contracts.*')) !!}
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Engage</h6>
          </li>
          {!! sideLink('Leaderboard','bi-trophy-fill',route('leaderboard.index'),request()->routeIs('leaderboard.*')) !!}
          {!! sideLink('Rewards','bi-gift-fill',route('rewards.index'),request()->routeIs('rewards.*')) !!}
          {!! sideLink('Messages','bi-chat-dots-fill',route('messages.index'),request()->routeIs('messages.*')) !!}
          {!! sideLink('Profile','bi-person-circle',route('profile.edit'),request()->routeIs('profile.*')) !!}

        @elseif($role==='hr')
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">HR Panel</h6>
          </li>
          {!! sideLink('Dashboard','bi-grid-fill',route('hr.dashboard'),request()->routeIs('hr.dashboard')) !!}
          {!! sideLink('Employees','bi-people-fill',route('hr.employees.index'),request()->routeIs('hr.employees.*')) !!}
          {!! sideLink('Leave Approvals','bi-calendar2-x-fill',route('hr.leaves.index'),request()->routeIs('hr.leaves.*')) !!}
          {!! sideLink('Complaints','bi-exclamation-octagon-fill',route('hr.complaints.index'),request()->routeIs('hr.complaints.*')) !!}
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Finance</h6>
          </li>
          {!! sideLink('Payroll','bi-cash-coin',route('hr.payrolls.index'),request()->routeIs('hr.payrolls.*')) !!}
          {!! sideLink('Contracts','bi-file-earmark-text-fill',route('hr.contracts.index'),request()->routeIs('hr.contracts.*')) !!}
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Engage</h6>
          </li>
          {!! sideLink('Leaderboard','bi-trophy-fill',route('leaderboard.index'),request()->routeIs('leaderboard.*')) !!}
          {!! sideLink('Rewards','bi-gift-fill',route('rewards.index'),request()->routeIs('rewards.*')) !!}
          {!! sideLink('Messages','bi-chat-dots-fill',route('messages.index'),request()->routeIs('messages.*')) !!}
          {!! sideLink('Profile','bi-person-circle',route('profile.edit'),request()->routeIs('profile.*')) !!}

        @elseif($role==='employee')
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">My Workspace</h6>
          </li>
          {!! sideLink('Dashboard','bi-grid-fill',route('employee.dashboard'),request()->routeIs('employee.dashboard')) !!}
          {!! sideLink('My Leaves','bi-calendar2-x-fill',route('employee.leaves.index'),request()->routeIs('employee.leaves.*')) !!}
          {!! sideLink('Grievances','bi-exclamation-octagon-fill',route('employee.complaints.index'),request()->routeIs('employee.complaints.*')) !!}
          {!! sideLink('My Payslips','bi-receipt',route('employee.payrolls.index'),request()->routeIs('employee.payrolls.*')) !!}
          <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs text-secondary font-weight-bolder opacity-8">Engage</h6>
          </li>
          {!! sideLink('Leaderboard','bi-trophy-fill',route('leaderboard.index'),request()->routeIs('leaderboard.*')) !!}
          {!! sideLink('Rewards','bi-gift-fill',route('rewards.index'),request()->routeIs('rewards.*')) !!}
          {!! sideLink('Messages','bi-chat-dots-fill',route('messages.index'),request()->routeIs('messages.*')) !!}
          {!! sideLink('Profile','bi-person-circle',route('profile.edit'),request()->routeIs('profile.*')) !!}
        @endif
      @endauth
    </ul>
  </div>

  @auth
    <div class="sidenav-footer mx-3 mb-3">
      <hr class="horizontal dark mb-2 mt-0">
      <div class="d-flex align-items-center px-1 py-1 gap-2">
      <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-dark flex-shrink-0 ui-sidenav-user-avatar">
          {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>
        <div class="overflow-hidden flex-grow-1">
          <p class="text-dark text-xs font-weight-bold mb-0 text-truncate">{{ auth()->user()->name }}</p>
          <p class="mb-0 text-capitalize text-secondary ui-sidenav-user-role">{{ auth()->user()->role }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" title="Sign Out" class="btn btn-sm btn-icon-only border-0 bg-transparent text-dark opacity-6 p-1">
            <i class="bi bi-box-arrow-right fs-6"></i>
          </button>
        </form>
      </div>
    </div>
  @endauth
</aside>
