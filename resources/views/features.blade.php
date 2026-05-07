@extends('layouts.landing')
@section('title', 'Features')

@section('content')
<section class="landing-section landing-page-shell features-page">
    <div class="container landing-container">
        <div class="landing-section-head text-center">
            <div class="landing-badge mx-auto">
                <i class="bi bi-grid-1x2"></i>
                <span>Core Features</span>
            </div>
            <h1 class="landing-section-title">Everything in one place</h1>
            <p class="landing-section-subtitle">
                A dashboard-first experience for Admin, HR, and Employees with the workflows needed to run a compliant workplace.
            </p>
        </div>

        <div class="feature-highlight card">
            <div class="card-body">
                <div>
                    <span class="feature-kicker">Unified HR command center</span>
                    <h2>Track people, requests, payroll, performance, and alerts from one system.</h2>
                    <p>
                        ComplianceSys connects routine HR work with clear visibility, so records stay organized and every role has a focused workspace.
                    </p>
                </div>
                <div class="feature-highlight-metrics">
                    <div><strong>Admin</strong><span>Full control</span></div>
                    <div><strong>HR</strong><span>Approvals and records</span></div>
                    <div><strong>Employee</strong><span>Self-service access</span></div>
                </div>
            </div>
        </div>

        <div class="feature-grid">
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-building"></i></div>
                <h3 class="landing-feature-title">Employee Management</h3>
                <p class="landing-feature-text">Maintain employee profiles, departments, roles, contact details, and structured workforce records.</p>
                <ul>
                    <li>Department-wise organization</li>
                    <li>Employee profile and code tracking</li>
                    <li>Admin and HR record controls</li>
                </ul>
            </div>
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-calendar2-check"></i></div>
                <h3 class="landing-feature-title">Attendance & Leaves</h3>
                <p class="landing-feature-text">Support daily attendance visibility and leave request handling from employee submission to HR approval.</p>
                <ul>
                    <li>Leave request status tracking</li>
                    <li>Attendance history visibility</li>
                    <li>Manager review workflow</li>
                </ul>
            </div>
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-wallet2"></i></div>
                <h3 class="landing-feature-title">Payroll & Payslips</h3>
                <p class="landing-feature-text">Help teams calculate salary records, review payroll data, and give employees access to payslip history.</p>
                <ul>
                    <li>Monthly payroll records</li>
                    <li>Employee payslip access</li>
                    <li>Admin and HR payroll screens</li>
                </ul>
            </div>
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-file-earmark-text"></i></div>
                <h3 class="landing-feature-title">Contract Tracking</h3>
                <p class="landing-feature-text">Keep contract records visible and reduce missed deadlines with expiry-focused tracking.</p>
                <ul>
                    <li>Contract creation and updates</li>
                    <li>Expiry reminders</li>
                    <li>Central contract history</li>
                </ul>
            </div>
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-chat-left-text"></i></div>
                <h3 class="landing-feature-title">Grievance Module</h3>
                <p class="landing-feature-text">Give employees a clear way to submit complaints and help HR teams handle issues with better tracking.</p>
                <ul>
                    <li>Employee complaint submission</li>
                    <li>Status-based handling</li>
                    <li>Admin and HR oversight</li>
                </ul>
            </div>
            <div class="landing-feature card">
                <div class="landing-feature-icon"><i class="bi bi-trophy"></i></div>
                <h3 class="landing-feature-title">Rewards & Leaderboard</h3>
                <p class="landing-feature-text">Encourage participation with performance scores, reward points, redeemable vouchers, and public ranking views.</p>
                <ul>
                    <li>Performance-based points</li>
                    <li>Reward redemption center</li>
                    <li>Leaderboard visibility</li>
                </ul>
            </div>
        </div>

        <div class="feature-workflow card">
            <div class="card-body">
                <div>
                    <span class="feature-kicker">Workflow coverage</span>
                    <h2>Built around the daily actions teams repeat most.</h2>
                </div>
                <div class="feature-workflow-list">
                    <div><i class="bi bi-person-check"></i><span>Employee self-service</span></div>
                    <div><i class="bi bi-bell"></i><span>Alerts and reminders</span></div>
                    <div><i class="bi bi-bar-chart"></i><span>Dashboard analytics</span></div>
                    <div><i class="bi bi-envelope"></i><span>Internal messages</span></div>
                </div>
            </div>
        </div>

        <div class="landing-bottom-cta card">
            <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="cta-title">Ready to try the dashboard?</div>
                    <div class="text-muted">Create an account and start managing compliance in minutes.</div>
                </div>
                <div class="landing-cta">
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
