@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'full' => false,
    'loading' => false,
    'disabled' => false,
])

@php
    $variantMap = [
        'primary' => 'btn-primary-grobdi',
        'secondary' => 'btn-secondary-grobdi',
        'success' => 'btn-success-grobdi',
        'warning' => 'btn-warning-grobdi',
        'danger' => 'btn-danger-grobdi',
        'info' => 'btn-info-grobdi',
        'outline' => 'btn-outline-grobdi',
        'outline-primary' => 'btn-outline-primary-grobdi',
        'ghost' => 'btn-ghost-grobdi',
        'link' => 'btn-link-grobdi',
    ];

    $sizeMap = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $classes = trim('btn-grobdi ' . ($variantMap[$variant] ?? $variantMap['primary']) . ' ' . ($sizeMap[$size] ?? ''));

    if ($full) {
        $classes .= ' btn-block';
    }

    if ($loading) {
        $classes .= ' btn-loading';
    }

    $iconMarkup = $icon ? '<i class="' . e($icon) . '"></i>' : '';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'role' => 'button']) }}>
        @if ($icon && $iconPosition === 'left')
            {!! $iconMarkup !!}
        @endif
        <span>{{ $slot }}</span>
        @if ($icon && $iconPosition === 'right')
            {!! $iconMarkup !!}
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }} @if($disabled) disabled @endif>
        @if ($icon && $iconPosition === 'left')
            {!! $iconMarkup !!}
        @endif
        <span>{{ $slot }}</span>
        @if ($icon && $iconPosition === 'right')
            {!! $iconMarkup !!}
        @endif
    </button>
@endif
