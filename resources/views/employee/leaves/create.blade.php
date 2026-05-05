@extends('layouts.app')

@section('title', 'Apply for Leave')

@section('content')
    <div class="mb-4">
        <x-button href="{{ route('employee.leaves.index') }}" type="secondary" icon="bi bi-arrow-left">Back</x-button>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Leave Application Form" icon="bi bi-calendar-plus">
                <form action="{{ route('employee.leaves.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required min="{{ date('Y-m-d') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Leave Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="casual" {{ old('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="earned" {{ old('type') == 'earned' ? 'selected' : '' }}>Earned Leave</option>
                            <option value="unpaid" {{ old('type') == 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required placeholder="Please provide a brief reason for your leave...">{{ old('reason') }}</textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid">
                        <x-button type="primary" icon="bi bi-send">Submit Application</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
