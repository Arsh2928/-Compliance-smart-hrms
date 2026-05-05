@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'autocomplete' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
])

@php
    $inputAttributes = ['class' => 'form-control', 'type' => $type];

    if ($name !== null) {
        $inputAttributes['name'] = $name;
    }

    if ($id !== null) {
        $inputAttributes['id'] = $id;
    }

    if ($autocomplete !== null) {
        $inputAttributes['autocomplete'] = $autocomplete;
    }

    if ($placeholder !== null) {
        $inputAttributes['placeholder'] = $placeholder;
    }
@endphp

<input
    {{ $attributes->merge($inputAttributes) }}
    @if($value !== null) value="{{ $value }}" @endif
    @if($required) required @endif
    @if($autofocus) autofocus @endif
/>