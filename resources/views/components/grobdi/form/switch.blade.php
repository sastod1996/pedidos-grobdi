@props([
    'label' => null,
    'name',
    'id' => null,
    'value' => '1',
    'checked' => null,
    'description' => null,
    'required' => false,
    'disabled' => false,
    'containerClass' => 'form-group-grobdi',
    'errorKey' => null,
    'errorMessage' => null,
    'includeHidden' => true,
    'uncheckedValue' => '0',
])
@php
    use Illuminate\Support\Str;

    $fieldId = $id ?? Str::slug($name . '-switch');
    $errorField = $errorKey ?? $name;
    $oldValue = old($name);

    if (!is_null($oldValue)) {
        $isChecked = $oldValue === true
            || $oldValue === 'on'
            || (string) $oldValue === (string) $value
            || (is_numeric($oldValue) && (string) $oldValue === (string) $value);
    } elseif (!is_null($checked)) {
        $isChecked = (bool) $checked;
    } else {
        $isChecked = false;
    }
@endphp

<div {{ $attributes->class([$containerClass]) }}>
    @if(!empty($label))
        <label class="grobdi-label">{!! $label !!}</label>
    @endif

    @if($includeHidden)
        <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
    @endif

    <label class="grobdi-switch" for="{{ $fieldId }}">
        <input
            type="checkbox"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if(!empty($required)) required @endif
            @if(!empty($disabled)) disabled @endif
        >
        <span class="switch-slider"></span>
        <span class="switch-label">
            {!! $description ?? ' ' !!}
        </span>
    </label>

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror
</div>
