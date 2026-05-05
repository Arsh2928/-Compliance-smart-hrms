@extends('layouts.app')
@section('title', 'Contracts')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-file-earmark-text-fill me-2 text-info"></i>Employee Contracts</h4>
    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Contract
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table contracts-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                @php
                    $isExpiring = \Carbon\Carbon::parse($contract->end_date)->isBefore(now()->addDays(30))
                                  && $contract->status === 'active';
                @endphp
                <tr class="{{ $isExpiring ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold" style="font-size:0.87rem;">
                            {{ $contract->employee->user->name ?? 'Unknown' }}
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('M d, Y') }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}
                        @if($isExpiring)
                            <span class="badge bg-danger ms-1">Expiring Soon</span>
                        @endif
                    </td>
                    <td class="table-status-cell">
                        @if($contract->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($contract->status === 'expired')
                            <span class="badge bg-danger">Expired</span>
                        @else
                            <span class="badge bg-secondary">Terminated</span>
                        @endif
                    </td>
                    <td class="contract-actions-cell">
                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-fill me-1"></i>Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-file-earmark-x"></i>
                            <p>No contracts found. <a href="{{ route('admin.contracts.create') }}">Add one now</a>.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contracts->hasPages())
    <div class="card-body pt-2">{{ $contracts->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
