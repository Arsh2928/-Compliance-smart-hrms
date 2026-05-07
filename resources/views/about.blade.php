@extends('layouts.landing')
@section('title', 'About Us')

@section('content')
<section class="landing-section landing-page-shell about-page">
    <div class="container landing-container">
        <div class="row align-items-center g-4 g-xl-5">
            <div class="col-lg-6">
                <div class="landing-badge">
                    <i class="bi bi-stars"></i>
                    <span>Built for workplace compliance</span>
                </div>
                <h1 class="landing-title">About ComplianceSys</h1>
                <p class="landing-subtitle">
                    ComplianceSys is a role-based labour law compliance platform that helps HR teams, admins, and employees manage daily workforce operations with clear records and better accountability.
                </p>

                <div class="about-stat-grid">
                    <div class="about-stat">
                        <span>3</span>
                        <small>User roles</small>
                    </div>
                    <div class="about-stat">
                        <span>8+</span>
                        <small>Core modules</small>
                    </div>
                    <div class="about-stat">
                        <span>24/7</span>
                        <small>Record visibility</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="about-visual card">
                    <div class="card-body">
                        <div class="about-visual-top">
                            <div class="landing-visual-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <span>Audit-ready workspace</span>
                        </div>
                        <h3 class="landing-feature-title">Designed for practical HR operations</h3>
                        <p class="landing-feature-text">
                            Admins get control, HR managers get operational clarity, and employees get simple self-service flows for attendance, leave, payroll, grievances, and rewards.
                        </p>
                        <div class="about-check-list">
                            <div><i class="bi bi-check2-circle"></i> Role-based dashboards and permissions</div>
                            <div><i class="bi bi-check2-circle"></i> Central records for employees, departments, payroll, and contracts</div>
                            <div><i class="bi bi-check2-circle"></i> Alerts for contract expiry, grievances, and important activity</div>
                        </div>
                        <div class="landing-cta">
                            <a href="{{ route('features') }}" class="btn btn-primary">See Features</a>
                            <a href="{{ route('contact') }}" class="btn btn-secondary">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-info-grid">
            <div class="about-info-card card">
                <div class="card-body">
                    <i class="bi bi-bullseye"></i>
                    <h3>Our purpose</h3>
                    <p>
                        The project reduces manual follow-ups and scattered paperwork by bringing attendance, leave, complaints, payroll, contracts, and communication into one dashboard-first system.
                    </p>
                </div>
            </div>
            <div class="about-info-card card">
                <div class="card-body">
                    <i class="bi bi-diagram-3"></i>
                    <h3>How it works</h3>
                    <p>
                        Each user sees only what matters to their role. Admin and HR users manage records and approvals, while employees submit requests, view payslips, raise grievances, and track rewards.
                    </p>
                </div>
            </div>
            <div class="about-info-card card">
                <div class="card-body">
                    <i class="bi bi-clipboard-data"></i>
                    <h3>Compliance focus</h3>
                    <p>
                        The system keeps structured data for key HR processes, making it easier to review workforce activity, maintain transparency, and respond quickly when action is required.
                    </p>
                </div>
            </div>
        </div>

        <div class="about-process card">
            <div class="card-body">
                <div class="landing-section-head">
                    <div class="landing-badge">
                        <i class="bi bi-list-check"></i>
                        <span>Operating flow</span>
                    </div>
                    <h2 class="landing-section-title">From request to record</h2>
                    <p class="landing-section-subtitle">Every common HR action is designed to move through a clear, traceable path.</p>
                </div>
                <div class="about-process-grid">
                    <div>
                        <span>01</span>
                        <h4>Employee submits</h4>
                        <p>Leaves, grievances, attendance updates, and profile actions begin from a simple employee workspace.</p>
                    </div>
                    <div>
                        <span>02</span>
                        <h4>HR reviews</h4>
                        <p>HR teams can approve, reject, update records, resolve complaints, and manage payroll or contract items.</p>
                    </div>
                    <div>
                        <span>03</span>
                        <h4>System tracks</h4>
                        <p>Dashboards, alerts, activity logs, and reward data help the organization retain context over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
