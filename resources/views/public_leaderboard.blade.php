@extends('layouts.landing')
@section('title', 'Public Leaderboard')

@section('content')
<div class="container py-5" style="margin-top: 60px;">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <span class="badge bg-gradient-primary mb-3">ComplianceSys Leaderboard</span>
            <h2 class="display-5 font-weight-bolder mb-2">Performance Rankings</h2>
            <p class="lead text-secondary">
                Ranking for {{ DateTime::createFromFormat('Y-m', $month)->format('F Y') }}
            </p>
        </div>
    </div>

    @if($allRecords->isEmpty())
        <div class="card shadow-sm text-center p-5 border-0">
            <i class="bi bi-stars text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">No Performance Data Yet</h5>
        </div>
    @else
        {{-- TOP 3 PODIUM --}}
        @if(count($allRecords) >= 1)
        <div class="row mb-5 align-items-end justify-content-center text-center g-3">
            {{-- RANK 2 --}}
            @if(isset($allRecords[1]))
            @php $p = $allRecords[1]->employee; $u = $p->user; @endphp
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(145deg,#f8fafc,#e2e8f0);">
                    <div class="card-body pt-4 pb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                             style="width:48px;height:48px;background:#64748b;font-size:1.1rem;">
                            {{ substr($u->name, 0, 1) }}
                        </div>
                        <div class="fw-bold" style="font-size:0.88rem;">{{ $u->name }}</div>
                        <div class="text-muted small">{{ $p->department->name ?? 'Staff' }}</div>
                        <div class="mt-2">🥈 <span class="badge bg-secondary">{{ $allRecords[1]->calculated_score }} pts</span></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- RANK 1 --}}
            @if(isset($allRecords[0]))
            @php $p = $allRecords[0]->employee; $u = $p->user; @endphp
            <div class="col-4 col-md-4" style="z-index:2;">
                <div class="card border-warning shadow" style="background: linear-gradient(145deg,#fffbeb,#fef3c7); transform:scale(1.05);">
                    <div class="card-body pt-4 pb-4">
                        <div class="rounded-circle text-dark d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                             style="width:56px;height:56px;background:#fbbf24;font-size:1.3rem;">
                            {{ substr($u->name, 0, 1) }}
                        </div>
                        <h5 class="fw-bold mb-0" style="font-size:0.95rem;">{{ $u->name }}</h5>
                        <div class="text-muted small">{{ $p->department->name ?? 'Staff' }}</div>
                        <div class="mt-2">🏆 <span class="badge bg-warning text-dark fs-6">{{ $allRecords[0]->calculated_score }} pts</span></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- RANK 3 --}}
            @if(isset($allRecords[2]))
            @php $p = $allRecords[2]->employee; $u = $p->user; @endphp
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(145deg,#fff7ed,#ffedd5);">
                    <div class="card-body pt-4 pb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-2"
                             style="width:48px;height:48px;background:#cd7f32;font-size:1.1rem;">
                            {{ substr($u->name, 0, 1) }}
                        </div>
                        <div class="fw-bold" style="font-size:0.88rem;">{{ $u->name }}</div>
                        <div class="text-muted small">{{ $p->department->name ?? 'Staff' }}</div>
                        <div class="mt-2">🥉 <span class="badge" style="background:#cd7f32;">{{ $allRecords[2]->calculated_score }} pts</span></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- FULL TABLE --}}
        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:70px;">Rank</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Percentile</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allRecords as $record)
                            @php 
                                $emp = $record->employee; 
                                $user = $emp->user; 
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="fw-bold fs-5 text-muted">#{{ $record->dynamic_rank }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                             style="width:38px;height:38px;background:#64748b;flex-shrink:0;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $emp->employee_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $emp->department->name ?? 'Staff' }}</td>
                                <td class="text-center fw-bold fs-6">{{ $record->calculated_score }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">Top {{ $record->dynamic_percentile }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
