@props([
    'label' => null,
    'name',
    'id' => null,
    'value' => null,
    'rows' => 3,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'description' => null,
    'errorKey' => null,
    'errorMessage' => null,
    'containerClass' => 'form-group-grobdi',
    'inputClass' => '',
    'inputAttrs' => [],
    'autosize' => false,
])
@php
    use Illuminate\View\ComponentAttributeBag;

    $fieldId = $id ?? $name;
    $errorField = $errorKey ?? $name;
    $hasError = $errors->has($errorField ?? '');
    $fieldValue = old($name, $value ?? '');
    $inputAttrBag = new ComponentAttributeBag($inputAttrs ?? []);
    $inputClasses = trim('grobdi-input ' . ($inputClass ?? '') . ($hasError ? ' is-invalid' : ''));
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

    <textarea
        name="{{ $name }}"
        id="{{ $fieldId }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ $inputAttrBag->merge(['class' => $inputClasses]) }}
        @if(!empty($required)) required @endif
        @if(!empty($disabled)) disabled @endif
        @if(!empty($readonly)) readonly @endif
        @if(!empty($autosize)) data-autosize="true" @endif
    >{{ $fieldValue }}</textarea>

    @if(!empty($description))
        <p class="field-description">{!! $description !!}</p>
    @endif

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror

    {{ $slot ?? '' }}
</div>
