@extends('layouts.landing')
@section('title', 'Home')

@section('content')
<section class="landing-hero">
    <div class="container landing-container">
        <div class="landing-hero-inner">
            <div class="landing-hero-copy">
                <div class="landing-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>Compliance-first HR platform</span>
                </div>
                <h1 class="landing-title">Simplify Labour Law Compliance</h1>
                <p class="landing-subtitle">
                    Manage attendance, leave, payroll, and contracts seamlessly—while staying audit-ready with automated alerts and clean reporting.
                </p>
                <div class="landing-cta">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get Started</a>
                    <a href="{{ route('features') }}" class="btn btn-secondary btn-lg">Explore Features</a>
                </div>
                <div class="landing-metrics">
                    <div class="landing-metric">
                        <div class="landing-metric-value">1-click</div>
                        <div class="landing-metric-label">attendance</div>
                    </div>
                    <div class="landing-metric">
                        <div class="landing-metric-value">Auto</div>
                        <div class="landing-metric-label">compliance alerts</div>
                    </div>
                    <div class="landing-metric">
                        <div class="landing-metric-value">Role</div>
                        <div class="landing-metric-label">based dashboards</div>
                    </div>
                </div>
            </div>

            <div class="landing-hero-card card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon" style="width: 44px; height: 44px; font-size: 1.15rem;">
                            <i class="bi bi-graph-up-arrow"></i>
                        </span>
                        <div>
                            <div style="font-weight: 800;">Today’s snapshot</div>
                            <div class="text-muted" style="font-size: 0.9rem;">A clean view, like your dashboard</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="dashboard-grid" style="margin-bottom: 0;">
                        <div class="stat-card card" style="margin-bottom: 0;">
                            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                            <div>
                                <div class="stat-value">8h</div>
                                <div class="stat-label">Avg. hours</div>
                            </div>
                        </div>
                        <div class="stat-card card" style="margin-bottom: 0;">
                            <div class="stat-icon"><i class="bi bi-calendar2-check"></i></div>
                            <div>
                                <div class="stat-value">12</div>
                                <div class="stat-label">Leaves</div>
                            </div>
                        </div>
                        <div class="stat-card card" style="margin-bottom: 0;">
                            <div class="stat-icon"><i class="bi bi-bell"></i></div>
                            <div>
                                <div class="stat-value">3</div>
                                <div class="stat-label">Alerts</div>
                            </div>
                        </div>
                    </div>
                    <div class="landing-mini-note">
                        Pro tip: login to view your role-based dashboard and alerts.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-section">
    <div class="container landing-container">
        <div class="landing-section-head">
            <h2 class="landing-section-title">Built for speed, clarity, and compliance</h2>
            <p class="landing-section-subtitle">Everything you need to stay organised—without the admin overload.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-clock-history"></i></div>
                    <h3 class="landing-feature-title">Attendance Tracking</h3>
                    <p class="landing-feature-text">Fast check-in/out with daily records and working hours calculations.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-calendar2-check"></i></div>
                    <h3 class="landing-feature-title">Leave Management</h3>
                    <p class="landing-feature-text">Apply, approve, and track leaves with a simple workflow.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h3 class="landing-feature-title">Compliance Alerts</h3>
                    <p class="landing-feature-text">Automated warnings for overtime, missed attendance, and contract expiry.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
