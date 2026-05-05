@props(['type' => 'primary', 'icon' => null, 'href' => null])

@php
    $class = 'btn btn-' . $type;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        @if($icon) <i class="{{ $icon }} me-2"></i> @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $class]) }}>
        @if($icon) <i class="{{ $icon }} me-2"></i> @endif
        {{ $slot }}
    </button>
@endif
