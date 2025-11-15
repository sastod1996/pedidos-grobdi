@props([
    'title' => null,
    'subtitle' => null,
    'badges' => [],
    'class' => '',
])

<section {{ $attributes->merge(['class' => trim('grobdi-header ' . $class)]) }}>
    <div class="grobdi-title">
        <div class="grobdi-title-body">
            @if ($title instanceof \Illuminate\Support\HtmlString)
                {!! $title !!}
            @elseif($title)
                <h3 class="card-title mb-0">{!! $title !!}</h3>
            @endif

            @if ($subtitle)
                <p class="mb-0 text-light">{!! $subtitle !!}</p>
            @endif

            @if (!empty($badges))
                <div class="badge-stack mt-2">
                    @foreach ($badges as $badge)
                        <span class="badge badge-grobdi {{ $badge['variant'] ?? 'badge-blue' }}">
                            {!! $badge['label'] ?? '' !!}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        @isset($actions)
            <div class="grobdi-header-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if (isset($filter))
        <div class="grobdi-filter">
            {{ $filter }}
        </div>
    @elseif (trim($slot))
        <div class="grobdi-filter">
            {{ $slot }}
        </div>
    @endif
</section>
