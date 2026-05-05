@extends('layouts.app')
@section('title', 'Sent Messages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-send me-2"></i>Sent Messages</h1>
    <a href="{{ route('messages.create') }}" class="btn btn-primary">
        <i class="bi bi-pencil-square me-1"></i> Compose
    </a>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Sent Items</span>
        <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary">Back to Inbox</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">To</th>
                        <th style="width: 50%">Subject</th>
                        <th style="width: 20%">Date</th>
                        <th style="width: 10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->receiver->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($message->subject, 50) }}</td>
                        <td>{{ $message->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('messages.show', $message) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-send-slash display-4 d-block mb-3"></i>
                            You haven't sent any messages yet.
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
