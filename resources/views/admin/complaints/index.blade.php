@extends('layouts.app')
@section('title', 'Complaints & Grievances')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>Complaints &amp; Grievances</h4>
    <span class="badge bg-danger fs-6">{{ $complaints->where('status','pending')->count() }} Open</span>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Submitted By</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr>
                    <td class="text-muted small">#{{ substr($complaint->id, -6) }}</td>
                    <td>
                        @if($complaint->is_anonymous)
                            @if(auth()->user()->role === 'admin')
                                <div><span class="badge bg-secondary me-1">Anon</span>
                                     <small class="text-muted">{{ $complaint->user->name ?? '?' }}</small></div>
                            @else
                                <span class="text-muted"><i class="bi bi-incognito me-1"></i>Anonymous</span>
                            @endif
                        @else
                            <div class="fw-semibold" style="font-size:0.85rem;">{{ $complaint->user->name ?? 'N/A' }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:0.87rem;">{{ Str::limit($complaint->title, 35) }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">{{ Str::limit($complaint->description, 50) }}</div>
                    </td>
                    <td class="small text-muted">{{ $complaint->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($complaint->status === 'resolved')
                            <span class="badge bg-success">Resolved</span>
                        @elseif($complaint->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning">Open</span>
                        @endif
                    </td>
                    <td>
                        @if($complaint->status === 'pending')
                        {{--
                            Pass complaint data via data-* attributes.
                            The single shared modal reads these on show.
                        --}}
                        @php
                            $updateUrl = auth()->user()->role === 'hr'
                                ? route('hr.complaints.update', $complaint)
                                : route('admin.complaints.update', $complaint);
                        @endphp
                        <button type="button" class="btn btn-sm btn-outline-primary respond-btn"
                                data-id="{{ $complaint->id }}"
                                data-title="{{ $complaint->title }}"
                                data-description="{{ $complaint->description }}"
                                data-url="{{ $updateUrl }}">
                            <i class="bi bi-reply-fill me-1"></i>Respond
                        </button>
                        @else
                        <button class="btn btn-sm btn-light text-muted" disabled>
                            <i class="bi bi-check-all"></i> Done
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-chat-square-text"></i>
                            <p>No complaints on record.</p>
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

{{--
    ═══════════════════════════════════════════════════════════
    SINGLE SHARED MODAL — lives outside the table at body level
    Populated by JS on button click. No more per-row modals.
    ═══════════════════════════════════════════════════════════
--}}
<div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="respondForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="respondModalLabel">
                        <i class="bi bi-reply-fill me-2 text-primary"></i>Respond to Complaint
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Complaint preview card --}}
                    <div class="p-3 mb-3 rounded" style="background: rgba(128, 128, 128, 0.1); border: 1px solid rgba(128, 128, 128, 0.2);">
                        <div class="fw-bold mb-1" id="modalComplaintTitle" style="font-size:0.92rem;"></div>
                        <p class="mb-0 opacity-7" id="modalComplaintDesc" style="font-size:0.82rem;"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select" required>
                            <option value="resolved">✅ Mark as Resolved</option>
                            <option value="rejected">❌ Reject Complaint</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Official Response</label>
                        <textarea name="admin_response" class="form-control" rows="4"
                                  required placeholder="Write your official response here…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Submit Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal    = document.getElementById('respondModal');
    const form     = document.getElementById('respondForm');
    const titleEl  = document.getElementById('modalComplaintTitle');
    const descEl   = document.getElementById('modalComplaintDesc');
    const bsModal  = new bootstrap.Modal(modal);

    // When any "Respond" button is clicked, populate the modal
    document.querySelectorAll('.respond-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url   = btn.dataset.url;
            const title = btn.dataset.title;
            const desc  = btn.dataset.description;

            // Set the form action to the correct complaint update URL
            form.action = url;

            // Populate the preview
            titleEl.textContent = title;
            descEl.textContent  = desc;

            // Reset the form fields each time
            form.querySelector('select[name="status"]').value = 'resolved';
            form.querySelector('textarea[name="admin_response"]').value = '';

            bsModal.show();
        });
    });
});
</script>
@endpush
