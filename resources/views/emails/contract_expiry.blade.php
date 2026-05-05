@component('mail::message')
# Contract Expiry Alert

This is a reminder that the contract for **{{ $contract->employee->user->name }}** ({{ $contract->employee->employee_code }}) is expiring on **{{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}**.

Please take necessary actions to renew or terminate.

@component('mail::button', ['url' => route('admin.contracts.index')])
View Contracts
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
