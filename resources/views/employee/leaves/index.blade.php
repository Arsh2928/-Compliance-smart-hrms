@extends('layouts.app')
@section('title', 'My Leave History')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-calendar2-x-fill me-2 text-primary"></i>My Leave History</h4>
    <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Apply for Leave
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table leave-table" style="table-layout:fixed; width:100%;">
            <colgroup>
                <col style="width:100px;">
                <col style="width:190px;">
                <col>{{-- Reason: takes remaining space --}}
                <col style="width:120px;">
                <col style="width:130px;">
            </colgroup>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td class="text-capitalize fw-medium">{{ $leave->type }}</td>
                    <td style="white-space:nowrap; font-size:0.82rem;">
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }}
                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                    </td>
                    <td style="max-width:0; overflow:hidden;">
                        <span style="display:block; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; color:var(--app-muted); font-size:0.85rem;"
                              title="{{ $leave->reason }}">
                            {{ $leave->reason }}
                        </span>
                    </td>
                    <td class="table-status-cell">
                        @if($leave->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($leave->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                        @if($leave->admin_remark)
                            <i class="bi bi-chat-left-text text-muted ms-1" data-bs-toggle="tooltip"
                               title="{{ $leave->admin_remark }}"></i>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $leave->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <p>No leave applications yet.
                                <a href="{{ route('employee.leaves.create') }}">Apply now</a>.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="card-body pt-2">{{ $leaves->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Enable Bootstrap tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endpush
