@props(['title' => null, 'icon' => null, 'headerAction' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $headerAction || $icon)
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                @if($icon) <i class="{{ $icon }}"></i> @endif
                {{ $title }}
            </h5>
            @if($headerAction)
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
