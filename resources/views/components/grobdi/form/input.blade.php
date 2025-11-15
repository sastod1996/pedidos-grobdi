@props([
    'label' => null,
    'name',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'description' => null,
    'errorKey' => null,
    'errorMessage' => null,
    'containerClass' => 'form-group-grobdi',
    'inputClass' => '',
    'inputAttrs' => [],
])
@php
    use Illuminate\View\ComponentAttributeBag;

    $fieldId = $id ?? $name;
    $errorField = $errorKey ?? $name;
    $isPassword = ($type ?? 'text') === 'password';
    $isFile = ($type ?? 'text') === 'file';
    $fieldValue = $isPassword || $isFile ? '' : old($name, $value ?? '');
    $hasError = $errors->has($errorField ?? '');
    $inputAttrBag = new ComponentAttributeBag($inputAttrs ?? []);
    $inputClasses = trim('grobdi-input ' . ($inputClass ?? '') . ($hasError ? ' is-invalid' : ''));
@endphp

<div {{ $attributes->class([$containerClass ?? 'form-group-grobdi']) }}>
    @if(!empty($label))
        <label for="{{ $fieldId }}" class="grobdi-label">
            {!! $label !!}
            @if(!empty($required))
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ $inputAttrBag->merge(['class' => $inputClasses]) }}
        @if(!empty($required)) required @endif
        @if(!empty($disabled)) disabled @endif
        @if(!empty($readonly)) readonly @endif
        @if(!empty($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
        @if(!empty($min)) min="{{ $min }}" @endif
        @if(!empty($max)) max="{{ $max }}" @endif
        @if(!empty($step)) step="{{ $step }}" @endif
        @unless($isFile)
            value="{{ $fieldValue }}"
        @endunless
    >

    @if(!empty($description))
        <p class="field-description">{!! $description !!}</p>
    @endif

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror

    {{ $slot ?? '' }}
</div>
