@props([
    'tabs' => [],
    'activeTab' => null,
    'title' => null,
    'subtitle' => null,
    'cardClass' => '',
])

@php
    $componentId = $attributes->get('id') ?? 'grobdi-tab-card-' . uniqid();
    $active = $activeTab ?? ($tabs[0]['id'] ?? null);
@endphp

<div {{ $attributes->merge(['class' => trim('card-grobdi tab-card-grobdi ' . $cardClass)]) }}>
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
            @isset($actions)
                <div class="tab-card-actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="tab-card-nav">
        <ul class="nav nav-tabs grobdi-nav-tabs" id="{{ $componentId }}-nav" role="tablist">
            @foreach ($tabs as $tab)
                @php
                    $isActive = ($tab['id'] ?? null) === $active;
                    $tabId = $tab['id'] ?? 'tab-' . $loop->index;
                @endphp
                <li class="nav-item">
                    <a
                        class="nav-link {{ $isActive ? 'active' : '' }}"
                        id="{{ $componentId }}-tab-{{ $tabId }}"
                        data-toggle="pill"
                        href="#{{ $tabId }}"
                        role="tab"
                        aria-controls="{{ $tabId }}"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    >
                        @if (!empty($tab['icon']))
                            <i class="{{ $tab['icon'] }} mr-1"></i>
                        @endif
                        {{ $tab['label'] ?? 'Tab ' . ($loop->index + 1) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="card-body-grobdi">
        <div class="tab-content" id="{{ $componentId }}-content">
            {{ $slot }}
        </div>
    </div>
</div>
