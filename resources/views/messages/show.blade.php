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
        
        @php
            $senderName = $message->sender_id ? $message->sender->name : ($message->guest_name ?? 'Unknown Sender');
            $senderEmail = $message->sender_id ? $message->sender->email : ($message->guest_email ?? 'No email provided');
            $initial = strtoupper(substr($senderName, 0, 1));
            $isReceiverMe = $message->receiver_id === auth()->id();
        @endphp

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;">
                    {{ $initial }}
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">{{ $senderName }} <small class="text-muted fw-normal">({{ $senderEmail }})</small></h6>
                    <small class="text-muted">To: {{ $isReceiverMe ? 'Me' : ($message->receiver->name ?? 'Unknown') }}</small>
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
        <div class="message-body" style="white-space: pre-wrap; font-size: 1rem; line-height: 1.6; color: #334155;">{{ $message->body }}</div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success m-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3">{{ session('error') }}</div>
    @endif

    @if($isReceiverMe)
    <div class="card-footer bg-white p-4">
        <h6 class="mb-3"><i class="bi bi-reply me-1"></i> Quick Reply</h6>
        <form method="POST" action="{{ route('messages.reply', $message->id) }}">
            @csrf
            <div class="mb-3">
                <textarea name="reply_body" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
