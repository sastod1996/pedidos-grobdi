@props([
    'label' => null,
    'value' => null,
    'icon' => null,
    'description' => null,
    'trend' => null,
    'trendLabel' => null,
    'variant' => 'primary',
])

@php
    $variantMap = [
        'primary' => 'stat-card-primary',
        'secondary' => 'stat-card-secondary',
        'success' => 'stat-card-success',
        'warning' => 'stat-card-warning',
        'danger' => 'stat-card-danger',
        'info' => 'stat-card-info',
    ];
    $trendClass = $trend === null ? '' : ($trend >= 0 ? 'text-success' : 'text-danger');
    $trendPrefix = $trend === null ? '' : ($trend >= 0 ? '+' : '');
@endphp

<div {{ $attributes->merge(['class' => 'card-grobdi stat-card-grobdi ' . ($variantMap[$variant] ?? $variantMap['primary'])]) }}>
    <div class="stat-card-header">
        <div class="stat-card-icon">
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @else
                <i class="fas fa-chart-bar"></i>
            @endif
        </div>
        <div class="stat-card-label text-muted">
            {{ $label }}
        </div>
    </div>
    <div class="stat-card-value">
        {{ $value ?? '—' }}
    </div>
    @if ($description)
        <p class="stat-card-description text-muted mb-0">{!! $description !!}</p>
    @endif
    @if ($trend !== null || $trendLabel)
        <div class="stat-card-trend {{ $trendClass }}">
            @if ($trend !== null)
                <span>{{ $trendPrefix }}{{ $trend }}%</span>
            @endif
            @if ($trendLabel)
                <small class="text-muted ml-2">{{ $trendLabel }}</small>
            @endif
        </div>
    @endif
</div>
