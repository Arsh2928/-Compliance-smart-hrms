@extends('layouts.app')

@section('title', 'Edit Contract')

@section('content')
    <div class="mb-4">
        <x-button href="{{ route('admin.contracts.index') }}" type="secondary" icon="bi bi-arrow-left">Back</x-button>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Edit Contract: {{ $contract->employee->user->name ?? 'Unknown' }}" icon="bi bi-pencil-square">
                <form action="{{ route('admin.contracts.update', $contract) }}" method="POST">
                    @csrf @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', substr($contract->start_date, 0, 10)) }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', substr($contract->end_date, 0, 10)) }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $contract->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('status', $contract->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('status', $contract->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid">
                        <x-button type="primary" icon="bi bi-save">Update Contract</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
