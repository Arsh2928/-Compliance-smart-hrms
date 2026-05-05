@extends('layouts.app')
@section('title', 'Leaderboard')

@section('content')
<div class="page-header mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-trophy-fill text-warning me-2"></i>Performance Leaderboard</h4>
        <p class="text-muted mb-0">
            Ranking for {{ DateTime::createFromFormat('Y-m', $month)->format('F Y') }}
        </p>
    </div>
    <form action="{{ route('leaderboard.index') }}" method="GET" class="d-flex gap-2">
        <select name="department_id" class="form-select" onchange="this.form.submit()" style="max-width:200px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
        <input type="month" name="month" class="form-control" value="{{ $month }}"
               max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" style="max-width:175px;">
    </form>
</div>

@if($leaderboard->isEmpty())
    <div class="card shadow-sm text-center p-5">
        <i class="bi bi-stars text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 text-muted">No Performance Data Yet</h5>
        <p class="text-muted small">Run <code>php artisan hr:evaluate-monthly-performance</code> to generate rankings.</p>
    </div>
@else

    {{-- ── TOP 3 PODIUM ──────────────────────────────────────────── --}}
    @if($leaderboard->currentPage() === 1 && count($topThree) > 0)
    <div class="row mb-5 align-items-end justify-content-center text-center g-3">

        {{-- RANK 2 --}}
        @if(isset($topThree[1]))
        @php $p = $topThree[1]; @endphp
        <div class="col-4 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(145deg,#f8fafc,#e2e8f0);">
                <div class="card-body pt-4 pb-3">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                         style="width:48px;height:48px;background:#64748b;font-size:1.1rem;">
                        {{ $p['initials'] }}
                    </div>
                    <div class="fw-bold" style="font-size:0.88rem;">{{ $p['name'] }}</div>
                    <div class="text-muted small">{{ $p['department'] }}</div>
                    <div class="mt-2">🥈 <span class="badge bg-secondary">{{ $p['final_score'] }} pts</span></div>
                    @if($p['rank_delta'] !== null)
                        <small class="text-{{ $p['rank_delta'] > 0 ? 'success' : ($p['rank_delta'] < 0 ? 'danger' : 'muted') }}">
                            {{ $p['delta_icon'] }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- RANK 1 --}}
        @if(isset($topThree[0]))
        @php $p = $topThree[0]; @endphp
        <div class="col-4 col-md-4" style="z-index:2;">
            <div class="card border-warning shadow" style="background: linear-gradient(145deg,#fffbeb,#fef3c7); transform:scale(1.05);">
                <div class="card-body pt-4 pb-4">
                    <div class="rounded-circle text-dark d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                         style="width:56px;height:56px;background:#fbbf24;font-size:1.3rem;">
                        {{ $p['initials'] }}
                    </div>
                    <h5 class="fw-bold mb-0" style="font-size:0.95rem;">{{ $p['name'] }}</h5>
                    <div class="text-muted small">{{ $p['department'] }}</div>
                    <div class="mt-2">🏆 <span class="badge bg-warning text-dark fs-6">{{ $p['final_score'] }} pts</span></div>
                    @if($p['rank_delta'] !== null)
                        <small class="text-{{ $p['rank_delta'] > 0 ? 'success' : ($p['rank_delta'] < 0 ? 'danger' : 'muted') }}">
                            {{ $p['delta_icon'] }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- RANK 3 --}}
        @if(isset($topThree[2]))
        @php $p = $topThree[2]; @endphp
        <div class="col-4 col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(145deg,#fff7ed,#ffedd5);">
                <div class="card-body pt-4 pb-3">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                         style="width:48px;height:48px;background:#cd7f32;font-size:1.1rem;">
                        {{ $p['initials'] }}
                    </div>
                    <div class="fw-bold" style="font-size:0.88rem;">{{ $p['name'] }}</div>
                    <div class="text-muted small">{{ $p['department'] }}</div>
                    <div class="mt-2">🥉 <span class="badge" style="background:#cd7f32;">{{ $p['final_score'] }} pts</span></div>
                    @if($p['rank_delta'] !== null)
                        <small class="text-{{ $p['rank_delta'] > 0 ? 'success' : ($p['rank_delta'] < 0 ? 'danger' : 'muted') }}">
                            {{ $p['delta_icon'] }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- ── FULL TABLE ─────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-list-ol text-primary me-2"></i>Full Rankings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:70px;">Rank</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Trend</th>
                            <th class="text-center">Tier</th>
                            <th class="text-center">Percentile</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $emp)
                        @php $isMe = (string)auth()->id() === (string)$emp['user_id']; @endphp
                        <tr class="{{ $isMe ? 'table-primary' : '' }}">
                            <td class="text-center">
                                <span class="fw-bold fs-5 text-muted">#{{ $emp['rank'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:38px;height:38px;background:{{ $isMe ? '#4f46e5' : '#64748b' }};flex-shrink:0;">
                                        {{ $emp['initials'] }}
                                    </div>
                                    <div>
                                        <div class="fw-bold {{ $isMe ? 'text-primary' : '' }}">
                                            {{ $emp['name'] }}
                                            @if($isMe) <span class="badge bg-primary ms-1">You</span> @endif
                                        </div>
                                        <small class="text-muted">{{ $emp['employee_code'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">{{ $emp['department'] }}</td>
                            <td class="text-center fw-bold fs-6">{{ $emp['final_score'] }}</td>
                            <td class="text-center">
                                @if($emp['rank_delta'] !== null)
                                    <span class="badge bg-{{ $emp['rank_delta'] > 0 ? 'success' : ($emp['rank_delta'] < 0 ? 'danger' : 'light text-muted') }}">
                                        {{ $emp['delta_icon'] }}
                                    </span>
                                @else
                                    <span class="text-muted small">New</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $tier = $emp['reward_tier'] ?? 'None'; @endphp
                                @if($tier === 'Gold')
                                    <span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Gold</span>
                                @elseif($tier === 'Silver')
                                    <span class="badge rounded-pill bg-secondary"><i class="bi bi-star-half me-1"></i>Silver</span>
                                @elseif($tier === 'Bronze')
                                    <span class="badge rounded-pill" style="background:#cd7f32;"><i class="bi bi-star me-1"></i>Bronze</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                                @if(!empty($emp['flags']))
                                    <i class="bi bi-flag-fill text-warning ms-1" title="{{ implode(', ', $emp['flags']) }}"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">Top {{ $emp['percentile'] }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($leaderboard->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $leaderboard->withQueryString()->links() }}
        </div>
        @endif
    </div>

@endif
@endsection
