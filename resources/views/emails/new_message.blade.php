@component('mail::message')
# New Message Received

**From:** {{ $messageObj->sender->name }}

**Subject:** {{ $messageObj->subject }}

{{ $messageObj->body }}

@component('mail::button', ['url' => route('messages.show', $messageObj->id)])
View Message
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
