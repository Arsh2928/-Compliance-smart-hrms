{{-- Argon Dashboard 3 top navbar (Bootstrap 5) --}}
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl position-sticky top-0 z-index-sticky bg-gray-100" id="navbarBlur" navbar-scroll="true">
  <div class="container-fluid py-2 px-3">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0">
          <li class="breadcrumb-item text-sm">
            <a class="opacity-5 text-dark" href="{{ route('home') }}">ComplianceSys</a>
          </li>
          <li class="breadcrumb-item text-sm text-dark active" aria-current="page">@yield('title','Dashboard')</li>
        </ol>
      </nav>
      <h6 class="font-weight-bolder mb-0">@yield('title','Dashboard')</h6>
    </div>

    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 justify-content-end" id="navbar">

      <ul class="navbar-nav justify-content-end align-items-center">
        <li class="nav-item pe-3 d-flex align-items-center">
          <button type="button" class="ui-theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
          </button>
        </li>

        @auth
          @php
            try {
              $unreadAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(6)->get();
              $unreadCount  = $unreadAlerts->count();
            } catch (\Exception $e) {
              $unreadAlerts = collect();
              $unreadCount  = 0;
            }
          @endphp

          <li class="nav-item dropdown pe-2 d-flex align-items-center">
            <a href="#" class="nav-link text-body p-0 position-relative" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-bell-fill cursor-pointer"></i>
              @if($unreadCount > 0)
                <span class="position-absolute badge bg-danger rounded-pill ui-nav-badge">
                  {{ $unreadCount }}
                </span>
              @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end px-2 py-3 shadow-lg border-0 ui-nav-dropdown">
              <div class="d-flex justify-content-between align-items-center px-2 mb-2">
                <h6 class="text-uppercase text-xs font-weight-bolder opacity-7 mb-0">Notifications</h6>
                @if($unreadCount > 0)
                  <form action="{{ route('alerts.read.all') }}" method="POST" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="btn btn-link text-xs text-primary p-0 m-0 text-decoration-none">Mark all as read</button>
                  </form>
                @endif
              </div>
              @forelse($unreadAlerts as $alert)
                <a href="{{ route('alerts.read', $alert) }}" class="dropdown-item border-radius-md">
                  <div class="d-flex py-1">
                    <div class="my-auto">
                      <div class="avatar avatar-sm bg-gradient-primary me-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-info-circle text-white"></i>
                      </div>
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="text-sm font-weight-normal mb-1">{{ \Illuminate\Support\Str::limit($alert->message, 70) }}</h6>
                      <p class="text-xs text-secondary mb-0">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                  </div>
                </a>
              @empty
                <div class="text-center py-3 text-secondary text-sm">No new notifications</div>
              @endforelse
            </div>
          </li>

          <li class="nav-item d-flex align-items-center">
            <a href="{{ route('profile.edit') }}" class="nav-link text-body font-weight-bold px-0 d-flex align-items-center gap-2">
              <div class="avatar avatar-sm bg-gradient-primary d-flex align-items-center justify-content-center ui-nav-avatar">
                <span class="text-white text-xs fw-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
              </div>
              <span class="d-none d-lg-inline text-sm">{{ auth()->user()->name }}</span>
            </a>
          </li>
        @endauth

        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav" aria-label="Toggle sidebar">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
