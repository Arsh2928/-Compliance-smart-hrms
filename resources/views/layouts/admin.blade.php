<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Labour Law Compliance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-end sidebar d-flex flex-column" id="sidebar-wrapper" style="width: 250px; transition: all 0.3s; position: sticky; top: 0; height: 100vh; overflow-y: auto;">
            <div class="sidebar-heading border-bottom text-white py-3 px-3 fs-5">Compliance App</div>
            <div class="list-group list-group-flush my-3 flex-grow-1">
                @php $role = auth()->user()->role; @endphp
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                
                @if($role === 'admin')
                <a href="{{ route('admin.employees.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-people me-2"></i> Employees
                </a>
                <a href="{{ route('admin.leaves.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-door-open me-2"></i> Leave Requests
                </a>
                <a href="{{ route('admin.complaints.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-chat-left-text me-2"></i> Complaints
                </a>
                <a href="{{ route('admin.payrolls.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-cash-stack me-2"></i> Payroll
                </a>
                <a href="{{ route('admin.contracts.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i> Contracts
                </a>
                <a href="{{ route('leaderboard.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-trophy me-2"></i> Leaderboard
                </a>
                @endif

                @if($role === 'hr')
                <a href="{{ route('hr.employees.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-people me-2"></i> Employees
                </a>
                <a href="{{ route('hr.leaves.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-door-open me-2"></i> Leave Requests
                </a>
                <a href="{{ route('hr.complaints.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-chat-left-text me-2"></i> Complaints
                </a>
                <a href="{{ route('hr.payrolls.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-cash-stack me-2"></i> Payroll
                </a>
                <a href="{{ route('hr.contracts.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i> Contracts
                </a>
                <a href="{{ route('leaderboard.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-trophy me-2"></i> Leaderboard
                </a>
                @endif

                @if($role === 'employee')
                <a href="#" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-calendar-check me-2"></i> My Attendance
                </a>
                <a href="{{ route('employee.leaves.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-door-open me-2"></i> My Leaves
                </a>
                <a href="{{ route('employee.payrolls.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-cash-stack me-2"></i> My Payroll
                </a>
                <a href="{{ route('employee.complaints.index') }}" class="list-group-item list-group-item-action bg-transparent text-white fw-bold">
                    <i class="bi bi-chat-left-text me-2"></i> Submit Complaint
                </a>
                @endif
            </div>
            <div class="mt-auto list-group list-group-flush mb-3">
                 <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold border-0 text-start">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-light">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 py-3">
                <button class="btn btn-outline-secondary" id="menu-toggle"><i class="bi bi-list"></i></button>
                <div class="ms-auto d-flex align-items-center">
                    <div class="me-3 position-relative">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </div>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            let sidebar = document.getElementById('sidebar-wrapper');
            if (sidebar.style.marginLeft === '-250px') {
                sidebar.style.marginLeft = '0';
            } else {
                sidebar.style.marginLeft = '-250px';
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
