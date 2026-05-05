@extends('layouts.app')
@section('title', 'Leave Requests')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-calendar2-x-fill me-2 text-primary"></i>Leave Requests</h4>
    <div>
        <span class="badge bg-warning fs-6">{{ $leaves->where('status','pending')->count() }} Pending</span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table leave-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:0.87rem;">
                            {{ $leave->employee->user->name ?? 'Unknown' }}
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $leave->employee->employee_code ?? '' }}
                        </div>
                    </td>
                    <td><span class="text-capitalize fw-medium">{{ $leave->type }}</span></td>
                    <td style="font-size:0.82rem; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                    </td>
                    <td class="table-text-cell">
                        <span class="table-truncate" title="{{ $leave->reason }}">
                            {{ Str::limit($leave->reason, 35) }}
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
                    </td>
                    <td class="table-actions-cell">
                        @if($leave->status === 'pending')
                        @php
                            $leaveUrl = auth()->user()->role === 'hr'
                                ? route('hr.leaves.update', $leave)
                                : route('admin.leaves.update', $leave);
                        @endphp
                        <div class="table-row-actions leave-row-actions">
                            <form action="{{ $leaveUrl }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success leave-approve-btn" title="Approve">
                                    <i class="bi bi-check-lg"></i><span>Approve</span>
                                </button>
                            </form>
                            <form action="{{ $leaveUrl }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Reject this leave?')" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                        @else
                            <span class="text-muted small"><i class="bi bi-check-all"></i> Processed</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <p>No leave requests found.</p>
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
