@props([
    'label' => null,
    'name',
    'options' => [],
    'idPrefix' => null,
    'value' => [],
    'required' => false,
    'description' => null,
    'errorKey' => null,
    'errorMessage' => null,
    'containerClass' => 'form-group-grobdi',
    'inline' => false,
])
@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $baseName = Str::before($name, '[');
    $fieldValues = collect(Arr::wrap(old($baseName, $value ?? [])))->map(fn($val) => (string) $val)->all();
    $errorField = $errorKey ?? $baseName;
    $groupClasses = trim('grobdi-checkbox-group ' . ($inline ? 'inline' : ''));
    $idPrefix = $idPrefix ?? Str::slug($baseName ?: 'checkbox');

    $normalizeOption = function ($option, $index) {
        if (is_array($option)) {
            return [
                'value' => (string) ($option['value'] ?? $index),
                'label' => $option['label'] ?? $option['value'] ?? '',
                'description' => $option['description'] ?? null,
                'disabled' => (bool) ($option['disabled'] ?? false),
            ];
        }

        if (is_object($option)) {
            return [
                'value' => (string) data_get($option, 'value', $index),
                'label' => data_get($option, 'label', data_get($option, 'value', '')),
                'description' => data_get($option, 'description'),
                'disabled' => (bool) data_get($option, 'disabled', false),
            ];
        }

        return [
            'value' => (string) $option,
            'label' => $option,
            'description' => null,
            'disabled' => false,
        ];
    };
@endphp

<div {{ $attributes->class([$containerClass]) }}>
    @if(!empty($label))
        <label class="grobdi-label">
            {!! $label !!}
            @if(!empty($required))
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="{{ $groupClasses }}">
        @foreach(collect($options)->values() as $index => $rawOption)
            @php($option = $normalizeOption($rawOption, $index))
            @php($optionId = $idPrefix . '_' . $index)
            <label class="grobdi-checkbox" for="{{ $optionId }}">
                <input
                    type="checkbox"
                    id="{{ $optionId }}"
                    name="{{ $name }}"
                    value="{{ $option['value'] }}"
                    @checked(in_array($option['value'], $fieldValues, true))
                    @if(!empty($required)) required @endif
                    @disabled($option['disabled'])
                >
                <span class="checkbox-custom"></span>
                <span class="checkbox-label">
                    {{ $option['label'] }}
                    @if(!empty($option['description']))
                        <span class="label-description">{{ $option['description'] }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>

    @if(!empty($description))
        <p class="field-description">{!! $description !!}</p>
    @endif

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror
</div>
