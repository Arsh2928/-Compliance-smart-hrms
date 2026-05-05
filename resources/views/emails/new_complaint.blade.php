@component('mail::message')
# New Complaint Submitted

A new complaint has been submitted by **{{ $complaint->is_anonymous ? 'Anonymous' : $complaint->user->name }}**.

**Subject:** {{ $complaint->title }}

@component('mail::button', ['url' => route('admin.complaints.index')])
View Complaint
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
