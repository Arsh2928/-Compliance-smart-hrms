@extends('layouts.app')
@section('title', 'Inbox')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-inbox me-2"></i>Inbox</h1>
    <a href="{{ route('messages.create') }}" class="btn btn-primary">
        <i class="bi bi-pencil-square me-1"></i> Compose
    </a>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Received Messages</span>
        <a href="{{ route('messages.sent') }}" class="btn btn-sm btn-outline-secondary">View Sent Messages</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">From</th>
                        <th style="width: 50%">Subject</th>
                        <th style="width: 20%">Date</th>
                        <th style="width: 10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi {{ $msg->is_read ? 'bi-envelope-open text-muted' : 'bi-envelope-fill text-primary' }} me-2"></i>
                                <span class="{{ $msg->is_read ? '' : 'fw-bold' }}">
                                    {{ $msg->sender_id ? $msg->sender->name : ($msg->guest_name ?? 'Guest') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('messages.show', $msg) }}" class="text-decoration-none text-dark {{ $msg->is_read ? '' : 'fw-bold' }}">
                                {{ \Illuminate\Support\Str::limit($msg->subject, 40) }}
                            </a>
                        </td>
                        <td class="text-muted small">
                            {{ $msg->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <a href="{{ route('messages.show', $msg) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-envelope-open display-4 d-block mb-3"></i>
                            Your inbox is empty.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($messages->hasPages())
    <div class="card-footer bg-white border-top-0">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection
