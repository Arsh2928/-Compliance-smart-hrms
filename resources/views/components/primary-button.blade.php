@props(['variant' => 'primary'])

@php
    $buttonClass = 'btn btn-' . $variant . ' w-100';
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => $buttonClass]) }}>
    {{ $slot }}
</button>
