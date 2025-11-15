@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'id' => null,
])

<div {{ $attributes->merge(['class' => 'card-grobdi chart-card-grobdi']) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="card-header-grobdi d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div>
                @if ($title)
                    <h4 class="mb-1">{!! $title !!}</h4>
                @endif
                @if ($subtitle)
                    <p class="mb-0 text-muted">{!! $subtitle !!}</p>
                @endif
            </div>
            @if (isset($actions))
                <div class="chart-card-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="card-body-grobdi">
        {{ $slot }}
    </div>
    @if (isset($footer))
        <div class="card-footer-grobdi">
            {{ $footer }}
        </div>
    @endif
</div>
