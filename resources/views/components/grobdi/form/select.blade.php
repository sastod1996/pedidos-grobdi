@props([
    'label' => null,
    'name',
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => '',
    'placeholderDisabled' => true,
    'required' => false,
    'disabled' => false,
    'description' => null,
    'errorKey' => null,
    'errorMessage' => null,
    'containerClass' => 'form-group-grobdi',
    'inputClass' => '',
    'inputAttrs' => [],
    'optionValueKey' => 'value',
    'optionLabelKey' => 'label',
    'optionDisabledKey' => 'disabled',
    'multiple' => false,
])

@php
    use Illuminate\Support\Arr;
    use Illuminate\View\ComponentAttributeBag;

    $fieldId = $id ?? $name;
    $errorField = $errorKey ?? $name;
    $hasError = $errors->has($errorField ?? '');
    $inputAttrBag = new ComponentAttributeBag($inputAttrs ?? []);
    $inputClasses = trim('grobdi-input ' . ($inputClass ?? '') . ($hasError ? ' is-invalid' : ''));

    $selectedValue = old($name, $value ?? ($multiple ? [] : ''));
    if ($multiple) {
        $selectedValue = array_map('strval', Arr::wrap($selectedValue));
    } else {
        $selectedValue = (string) $selectedValue;
    }

    $normalizeOption = function ($option) use ($optionValueKey, $optionLabelKey, $optionDisabledKey) {
        if (is_array($option)) {
            return [
                'value' => (string) ($option[$optionValueKey] ?? $option['value'] ?? ''),
                'label' => $option[$optionLabelKey] ?? $option['label'] ?? '',
                'disabled' => (bool) ($option[$optionDisabledKey] ?? $option['disabled'] ?? false),
            ];
        }

        if (is_object($option)) {
            return [
                'value' => (string) data_get($option, $optionValueKey, data_get($option, 'value', '')),
                'label' => data_get($option, $optionLabelKey, data_get($option, 'label', '')),
                'disabled' => (bool) data_get($option, $optionDisabledKey, data_get($option, 'disabled', false)),
            ];
        }

        return [
            'value' => (string) $option,
            'label' => $option,
            'disabled' => false,
        ];
    };

    $optionsCollection = collect($options)->map($normalizeOption);
@endphp

<div {{ $attributes->class([$containerClass]) }}>
    @if(!empty($label))
        <label for="{{ $fieldId }}" class="grobdi-label">
            {!! $label !!}
            @if(!empty($required))
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $fieldId }}"
        {{ $inputAttrBag->merge(['class' => $inputClasses]) }}
        @if(!empty($required)) required @endif
        @if(!empty($disabled)) disabled @endif
        @if($multiple) multiple @endif
    >
        @if($placeholder !== null && !$multiple)
            <option
                value="{{ $placeholderValue }}"
                @if($placeholderDisabled) disabled @endif
                @selected($selectedValue === (string) $placeholderValue)
            >
                {{ $placeholder }}
            </option>
        @endif

        @foreach($optionsCollection as $option)
            <option
                value="{{ $option['value'] }}"
                @selected(
                    $multiple
                        ? in_array($option['value'], $selectedValue, true)
                        : $selectedValue === $option['value']
                )
                @disabled($option['disabled'])
            >
                {{ $option['label'] }}
            </option>
        @endforeach

        {{ $slot ?? '' }}
    </select>

    @if(!empty($description))
        <p class="field-description">{!! $description !!}</p>
    @endif

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror
</div>
