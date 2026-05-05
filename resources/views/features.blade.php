@extends('layouts.landing')
@section('title', 'Features')

@section('content')
<section class="landing-section">
    <div class="container landing-container">
        <div class="landing-section-head text-center">
            <div class="landing-badge" style="margin-left:auto;margin-right:auto;">
                <i class="bi bi-grid-1x2"></i>
                <span>Core Features</span>
            </div>
            <h1 class="landing-section-title" style="margin-top: 14px;">Everything in one place</h1>
            <p class="landing-section-subtitle">A dashboard-first experience for HR, Admin, and Employees.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-building"></i></div>
                    <h3 class="landing-feature-title">Employee Management</h3>
                    <p class="landing-feature-text">Complete CRUD operations for departments and employees with clean records.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-wallet2"></i></div>
                    <h3 class="landing-feature-title">Payroll & Contracts</h3>
                    <p class="landing-feature-text">Salary calculations, overtime visibility, and contract expiry reminders.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="landing-feature card h-100">
                    <div class="landing-feature-icon"><i class="bi bi-chat-left-text"></i></div>
                    <h3 class="landing-feature-title">Grievance Module</h3>
                    <p class="landing-feature-text">Complaint submission and tracking with streamlined admin handling.</p>
                </div>
            </div>
        </div>

        <div class="landing-bottom-cta card" style="margin-top: 24px;">
            <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div style="font-weight: 900; font-size: 1.1rem;">Ready to try the dashboard?</div>
                    <div class="text-muted">Create an account and start managing compliance in minutes.</div>
                </div>
                <div class="landing-cta" style="margin: 0; justify-content: flex-end;">
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
