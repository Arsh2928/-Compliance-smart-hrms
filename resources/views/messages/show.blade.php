@extends('layouts.app')
@section('title', 'View Message')

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white p-4">
        <h3 class="mb-3">{{ $message->subject }}</h3>
        
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;">
                    {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">{{ $message->sender->name }}</h6>
                    <small class="text-muted">To: {{ $message->receiver_id === auth()->id() ? 'Me' : $message->receiver->name }}</small>
                </div>
            </div>
            <div class="text-end">
                <small class="text-muted">{{ $message->created_at->format('M d, Y h:i A') }}</small>
                <br>
                <small class="text-muted">({{ $message->created_at->diffForHumans() }})</small>
            </div>
        </div>
    </div>
    <div class="card-body p-4 bg-light">
        <div class="message-body" style="white-space: pre-wrap; font-size: 1rem; line-height: 1.6; color: #334155;">
{{ $message->body }}
        </div>
    </div>
    <div class="card-footer bg-white p-3 text-end">
        <a href="{{ route('messages.create', ['reply_to' => $message->sender_id]) }}" class="btn btn-primary">
            <i class="bi bi-reply me-1"></i> Reply
        </a>
    </div>
</div>
@endsection
