<nav class="top-navbar">
    <div class="d-flex align-items-center">
        <!-- Optional toggler for mobile (not implemented yet) -->
        <button class="btn btn-light d-md-none me-3"><i class="bi bi-list"></i></button>
        <h4 class="mb-0 text-dark fw-bold">@yield('title', 'Dashboard')</h4>
    </div>
    
    <div class="d-flex align-items-center">
        <!-- Alerts Dropdown -->
        @php
            $alerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(5)->get();
            $unreadCount = $alerts->count();
        @endphp
        <div class="dropdown me-4">
            <a href="#" class="text-secondary position-relative text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                @forelse($alerts as $alert)
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('alerts.read', $alert->id) }}">
                            <div class="me-3">
                                @if($alert->type == 'warning')
                                    <i class="bi bi-exclamation-circle text-warning fs-4"></i>
                                @elseif($alert->type == 'danger')
                                    <i class="bi bi-x-circle text-danger fs-4"></i>
                                @else
                                    <i class="bi bi-info-circle text-info fs-4"></i>
                                @endif
                            </div>
                            <div>
                                <p class="mb-0 text-wrap" style="font-size: 0.85rem;">{{ $alert->message }}</p>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $alert->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    </li>
                @empty
                    <li><span class="dropdown-item text-muted text-center py-3">No new alerts</span></li>
                @endforelse
            </ul>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light border-0 dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: transparent;">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight: 600;">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="fw-medium text-dark">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Log Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
