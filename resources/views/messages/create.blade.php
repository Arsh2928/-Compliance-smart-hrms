@extends('layouts.app')
@section('title', 'Compose Message')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-pencil-square me-2"></i>Compose Message</h1>
    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Inbox
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('messages.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="receiver_id" class="form-label fw-bold">To</label>
                <select name="receiver_id" id="receiver_id" class="form-select @error('receiver_id') is-invalid @enderror" required>
                    <option value="">Select a recipient...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ ucfirst($user->role) }})
                        </option>
                    @endforeach
                </select>
                @error('receiver_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label fw-bold">Subject</label>
                <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="body" class="form-label fw-bold">Message</label>
                <textarea name="body" id="body" rows="6" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send me-2"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
