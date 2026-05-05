@extends('layouts.app')
@section('title', 'Reward Center')

@section('content')
<div class="welcome-banner mb-4">
    <div>
        <h3 class="welcome-title">Reward Center 🎁</h3>
        <p class="welcome-subtitle">Convert your hard-earned performance points into real-world rewards.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="badge bg-warning text-dark fs-5 shadow-sm p-3 rounded-pill">
            <i class="bi bi-coin me-1"></i> {{ $employee->points ?? 0 }} Points
        </div>
    </div>
</div>

<div class="row g-4">
    @foreach($rewards as $reward)
    <div class="col-md-3">
        <div class="card h-100 text-center border-0 shadow-sm" style="border-radius: 16px; transition: transform 0.2s;">
            <div class="card-body p-4 d-flex flex-column align-items-center">
                <div class="icon-circle mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #fef08a, #facc15); color: #b45309; font-size: 1.5rem;">
                    <i class="bi {{ $reward['icon'] }}"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">{{ $reward['name'] }}</h6>
                <div class="text-muted small mb-3">Cost: <span class="fw-bold text-warning">{{ $reward['cost'] }} pts</span></div>
                
                <form action="{{ route('rewards.redeem') }}" method="POST" class="mt-auto w-100">
                    @csrf
                    <input type="hidden" name="reward_id" value="{{ $reward['id'] }}">
                    <input type="hidden" name="cost" value="{{ $reward['cost'] }}">
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill fw-bold" {{ ($employee->points ?? 0) < $reward['cost'] ? 'disabled' : '' }}>
                        {{ ($employee->points ?? 0) < $reward['cost'] ? 'Not Enough Pts' : 'Redeem Now' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
