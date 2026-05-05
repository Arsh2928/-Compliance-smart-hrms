@extends('layouts.app')
@section('title', 'My Complaints')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>My Grievances</h4>
    <a href="{{ route('employee.complaints.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Submit Complaint
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Submitted On</th>
                    <th>Visibility</th>
                    <th>Status</th>
                    <th>Response</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:0.87rem;">
                            {{ Str::limit($complaint->title, 45) }}
                        </div>
                    </td>
                    <td class="text-muted small">{{ $complaint->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($complaint->is_anonymous)
                            <span class="badge bg-secondary"><i class="bi bi-incognito me-1"></i>Anonymous</span>
                        @else
                            <span class="badge bg-info"><i class="bi bi-person me-1"></i>Public</span>
                        @endif
                    </td>
                    <td>
                        @if($complaint->status === 'resolved')
                            <span class="badge bg-success">Resolved</span>
                        @elseif($complaint->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning">Under Review</span>
                        @endif
                    </td>
                    <td>
                        @if($complaint->admin_response)
                        <button type="button" class="btn btn-sm btn-outline-info"
                                data-bs-toggle="modal" data-bs-target="#resp{{ $complaint->id }}">
                            <i class="bi bi-eye me-1"></i>View
                        </button>

                        @else
                            <span class="text-muted small">Awaiting response</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-chat-square-text"></i>
                            <p>No complaints submitted yet.
                                <a href="{{ route('employee.complaints.create') }}">Submit one</a>.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($complaints->hasPages())
    <div class="card-body pt-2">{{ $complaints->withQueryString()->links() }}</div>
    @endif
</div>

@foreach($complaints as $complaint)
    @if($complaint->admin_response)
        <div class="modal fade" id="resp{{ $complaint->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Official Response</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <strong>{{ $complaint->title }}</strong>
                            <p class="mb-0 mt-1 small text-muted">{{ $complaint->description }}</p>
                        </div>
                        <p class="mb-0">{{ $complaint->admin_response }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
