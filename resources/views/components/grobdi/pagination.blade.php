@props([
    'idPrefix' => null,
    'perPage' => null,
    'perPageLabel' => null,
    'infoText' => 'Página 1 de 1',
    'visible' => false,
    'wrapperClasses' => 'd-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3',
    'prevLabel' => 'Anterior',
    'nextLabel' => 'Siguiente',
    'prevDisabled' => true,
    'nextDisabled' => true,
])

@php
    $resolvedPrefix = $idPrefix ?: 'grobdiPagination_' . uniqid();
    $wrapperId = $resolvedPrefix;
    $infoId = $resolvedPrefix . 'Info';
    $prevId = $resolvedPrefix . 'Prev';
    $nextId = $resolvedPrefix . 'Next';

    $perPageCopy = null;
    if ($perPageLabel !== false) {
        $perPageCopy = $perPageLabel ?? ($perPage !== null
            ? sprintf('%s registros por página', number_format((int) $perPage, 0, ',', '.'))
            : null);
    }

    $wrapperClassList = trim($wrapperClasses . ' ' . ($visible ? '' : 'd-none'));
@endphp

<div {{ $attributes->merge(['class' => 'card-body-grobdi pt-0']) }}>
    <div id="{{ $wrapperId }}" class="{{ $wrapperClassList }}">
        @if($perPageCopy)
            <small class="text-muted mb-0">{{ $perPageCopy }}</small>
        @endif
        <div class="d-flex align-items-center gap-2">
            <button id="{{ $prevId }}" type="button" class="btn-grobdi btn-outline-primary-grobdi btn-sm" @if($prevDisabled) disabled @endif>
                {{ $prevLabel }}
            </button>
            <span id="{{ $infoId }}" class="text-muted small">{{ $infoText }}</span>
            <button id="{{ $nextId }}" type="button" class="btn-grobdi btn-outline-primary-grobdi btn-sm" @if($nextDisabled) disabled @endif>
                {{ $nextLabel }}
            </button>
        </div>
    </div>
</div>
