@extends('layouts.landing')
@section('title', 'About Us')

@section('content')
<section class="landing-section">
    <div class="container landing-container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="landing-badge">
                    <i class="bi bi-stars"></i>
                    <span>Built like a modern SaaS</span>
                </div>
                <h1 class="landing-title" style="margin-top: 14px;">About Our Project</h1>
                <p class="landing-subtitle">
                    We bridge the gap between strict labour law compliance and reducing day-to-day administrative burden for employees and HR teams.
                </p>
                <div class="landing-copy card">
                    <div class="card-body">
                        <p class="mb-3">
                            Our solution delivers a clean, role-based interface for Admins, HR Managers, and Employees to manage attendance,
                            leaves, payroll, and contracts—without losing visibility or audit readiness.
                        </p>
                        <div class="landing-bullets">
                            <div class="landing-bullet"><i class="bi bi-check2-circle"></i><span>Clear workflows</span></div>
                            <div class="landing-bullet"><i class="bi bi-check2-circle"></i><span>Automated alerts</span></div>
                            <div class="landing-bullet"><i class="bi bi-check2-circle"></i><span>Dashboard-style UX</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="landing-visual card">
                    <div class="card-body">
                        <div class="landing-visual-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="landing-feature-title" style="margin-top: 14px;">Designed for teams</h3>
                        <p class="landing-feature-text">
                            Admin and HR get oversight; employees get a simple daily flow. Everyone stays aligned.
                        </p>
                        <div class="landing-cta" style="justify-content: flex-start;">
                            <a href="{{ route('features') }}" class="btn btn-primary">See Features</a>
                            <a href="{{ route('contact') }}" class="btn btn-secondary">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
