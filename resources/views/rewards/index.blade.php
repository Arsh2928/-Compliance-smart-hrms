@extends('layouts.app')
@section('title', 'Reward Center')

@section('content')
@php
    $points = $employee->points ?? 0;
    $redeemedCount = count($myVouchers ?? []);
@endphp

<div class="welcome-banner reward-hero mb-4">
    <div class="reward-hero-copy">
        <span class="reward-eyebrow">Perks marketplace</span>
        <h3 class="welcome-title">Reward Center</h3>
        <p class="welcome-subtitle">Convert your hard-earned performance points into real-world rewards.</p>
    </div>
    <div class="reward-hero-stats">
        <div class="reward-points-pill">
            <i class="bi bi-coin"></i>
            <div>
                <span>{{ $points }}</span>
                <small>Available points</small>
            </div>
        </div>
        <div class="reward-mini-stat">
            <span>{{ $redeemedCount }}</span>
            <small>Vouchers</small>
        </div>
    </div>
</div>

<div class="reward-grid">
    @foreach($rewards as $reward)
        @php
            $canRedeem = $points >= $reward['cost'];
            $progress = min(100, $reward['cost'] > 0 ? round(($points / $reward['cost']) * 100) : 0);
            $remaining = max(0, $reward['cost'] - $points);
        @endphp
        <div class="reward-card card {{ $canRedeem ? 'is-available' : 'is-locked' }}">
            <div class="card-body">
                <div class="reward-icon">
                    <i class="bi {{ $reward['icon'] }}"></i>
                </div>
                <span class="reward-status">
                    {{ $canRedeem ? 'Ready to claim' : $remaining . ' pts away' }}
                </span>
                <h6>{{ $reward['name'] }}</h6>
                <div class="reward-cost">
                    <span>{{ $reward['cost'] }}</span> pts
                </div>
                <div class="reward-progress" aria-label="Reward progress">
                    <span style="width: {{ $progress }}%;"></span>
                </div>

                <form action="{{ route('rewards.redeem') }}" method="POST" class="mt-auto w-100">
                    @csrf
                    <input type="hidden" name="reward_id" value="{{ $reward['id'] }}">
                    <input type="hidden" name="reward_name" value="{{ $reward['name'] }}">
                    <input type="hidden" name="cost" value="{{ $reward['cost'] }}">
                    <button type="submit" class="btn reward-redeem-btn w-100" {{ !$canRedeem ? 'disabled' : '' }}>
                        <i class="bi {{ $canRedeem ? 'bi-stars' : 'bi-lock' }} me-2"></i>
                        {{ $canRedeem ? 'Redeem Now' : 'Not Enough Pts' }}
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>

<div class="reward-vouchers mt-5">
    <div class="reward-section-title mb-3">
        <i class="bi bi-wallet2"></i>
        <h4>My Vouchers</h4>
    </div>
    @if(empty($myVouchers))
        <div class="card reward-empty-card text-center">
            <div class="card-body">
                <i class="bi bi-inbox"></i>
                <h6>You haven't redeemed any rewards yet.</h6>
                <p>Use your points to claim rewards above and they will appear here.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($myVouchers as $voucher)
            <div class="col-md-4">
                <div class="card reward-voucher-card {{ $voucher['is_used'] ? 'is-used' : 'is-active' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge {{ $voucher['is_used'] ? 'bg-secondary' : 'bg-success' }}">
                                {{ $voucher['is_used'] ? 'Used' : 'Available' }}
                            </span>
                            <span class="text-xs text-muted">{{ \Carbon\Carbon::parse($voucher['redeemed_at'])->format('d M Y') }}</span>
                        </div>
                        <h6 class="fw-bold mb-1">{{ $voucher['reward_name'] ?? 'Reward Voucher' }}</h6>
                        <p class="text-xs text-muted mb-3 font-monospace">ID: {{ $voucher['voucher_id'] }}</p>

                        @if(!$voucher['is_used'])
                            <form action="{{ route('rewards.use') }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this voucher as used? This action cannot be undone.');">
                                @csrf
                                <input type="hidden" name="voucher_id" value="{{ $voucher['voucher_id'] }}">
                                <button type="submit" class="btn btn-primary btn-sm w-100 mb-0">Use Voucher</button>
                            </form>
                        @else
                            <button class="btn btn-outline-secondary btn-sm w-100 mb-0" disabled>
                                Used on {{ \Carbon\Carbon::parse($voucher['used_at'])->format('d M Y') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
