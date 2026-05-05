@props(['for' => null, 'value' => null])

<label {{ $attributes->merge(['for' => $for, 'class' => 'form-label']) }}>
    {{ $value ?? $slot }}
</label>
