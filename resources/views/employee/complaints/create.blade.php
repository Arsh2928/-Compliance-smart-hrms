@extends('layouts.app')

@section('title', 'Submit Complaint')

@section('content')
    <div class="mb-4">
        <x-button href="{{ route('employee.complaints.index') }}" type="secondary" icon="bi bi-arrow-left">Back</x-button>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Grievance Submission Form" icon="bi bi-exclamation-triangle">
                <form action="{{ route('employee.complaints.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Complaint Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="Briefly summarize your issue">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Detailed Description</label>
                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required placeholder="Provide all necessary details regarding your complaint...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4 bg-light p-3 rounded border">
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_anonymous" name="is_anonymous" value="1" style="width: 40px; height: 20px;">
                            <label class="form-check-label fw-bold" for="is_anonymous">Submit Anonymously</label>
                        </div>
                        <p class="text-muted small mb-0 mt-2">
                            If enabled, your identity will be hidden from HR managers. However, System Administrators may still be able to identify you for security purposes.
                        </p>
                    </div>

                    <div class="d-grid">
                        <x-button type="danger" icon="bi bi-send">Submit Complaint</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
