@props([
    'title' => null,
    'subtitle' => null,
    'cardClass' => '',
    'tableClass' => '',
    'responsive' => true,
    'tableId' => null,
])

@php
    $tableClasses = trim('table table-grobdi ' . $tableClass);
@endphp

<div {{ $attributes->merge(['class' => trim('card-grobdi table-card-grobdi ' . $cardClass)]) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="card-header-grobdi d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
            <div>
                @if ($title instanceof \Illuminate\Support\HtmlString)
                    {!! $title !!}
                @elseif ($title)
                    <h4 class="mb-1">{!! $title !!}</h4>
                @endif
                @if ($subtitle)
                    <p class="mb-0 text-muted">{!! $subtitle !!}</p>
                @endif
            </div>
            @isset($actions)
                <div class="table-card-actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="card-body-grobdi">
        @isset($toolbar)
            <div class="table-toolbar mb-3">
                {{ $toolbar }}
            </div>
        @endisset

        <div class="{{ $responsive ? 'table-responsive' : '' }}">
            <table @if ($tableId) id="{{ $tableId }}" @endif class="{{ $tableClasses }}">
                {{ $slot }}
            </table>
        </div>

        @isset($empty)
            <div class="table-empty-state mt-3">
                {{ $empty }}
            </div>
        @endisset
    </div>

    @isset($footer)
        <div class="card-footer-grobdi">
            {{ $footer }}
        </div>
    @endisset
</div>
