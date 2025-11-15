@props([
    'label',
    'name',
    'id' => null,
    'value' => '1',
    'checked' => null,
    'description' => null,
    'required' => false,
    'disabled' => false,
    'fieldLabel' => null,
    'errorKey' => null,
    'errorMessage' => null,
    'containerClass' => 'form-group-grobdi',
    'inputAttrs' => [],
    'includeHidden' => true,
    'uncheckedValue' => '0',
])
@php
    use Illuminate\Support\Str;
    use Illuminate\View\ComponentAttributeBag;

    $fieldId = $id ?? Str::slug($name . '-' . ($value ?? 'on'));
    $baseName = Str::before($name, '[');
    $errorField = $errorKey ?? $baseName;
    $inputAttrBag = new ComponentAttributeBag($inputAttrs ?? []);
    $oldValue = old($baseName);

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
    @if(!empty($fieldLabel))
        <label class="grobdi-label">{!! $fieldLabel !!}</label>
    @endif

    @if($includeHidden && !str_contains($name, '[]'))
        <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
    @endif

    <label class="grobdi-checkbox" for="{{ $fieldId }}">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $fieldId }}"
            value="{{ $value }}"
            {{ $inputAttrBag }}
            @if(!empty($required)) required @endif
            @if(!empty($disabled)) disabled @endif
            @checked($isChecked)
        >
        <span class="checkbox-custom"></span>
        <span class="checkbox-label">
            {!! $label !!}
            @if(!empty($description))
                <span class="label-description">{!! $description !!}</span>
            @endif
        </span>
    </label>

    @error($errorField)
        <div class="invalid-feedback d-block">{{ $errorMessage ?? $message }}</div>
    @enderror
</div>
