@props([
    'departamentosEndpoint',
    'provinciasEndpoint',
    'distritosEndpoint',
    'chainEndpoint',
    'selectedDepartamento' => null,
    'selectedProvincia' => null,
    'selectedDistrito' => null,
    'name' => 'distrito_id',
    'required' => false,
    'labelDepartamento' => 'Departamento',
    'labelProvincia' => 'Provincia',
    'labelDistrito' => 'Distrito',
    'errorKey' => null,
])

@php
    $fieldName = $name ?: 'distrito_id';
    $errorField = $errorKey ?: $fieldName;
    $initialDepartamento = old('departamento_id', $selectedDepartamento);
    $initialProvincia = old('provincia_id', $selectedProvincia);
    $initialDistrito = old($fieldName, $selectedDistrito);
    $uid = uniqid('loc_', false);
    $departamentoInputId = $uid . '_departamento';
    $provinciaInputId = $uid . '_provincia';
    $distritoInputId = $uid . '_distrito';
@endphp

<div class="row g-4"
    data-location-selector
    data-departamentos-endpoint="{{ $departamentosEndpoint }}"
    data-provincias-endpoint="{{ $provinciasEndpoint }}"
    data-distritos-endpoint="{{ $distritosEndpoint }}"
    data-chain-endpoint="{{ $chainEndpoint }}"
    data-initial-departamento="{{ $initialDepartamento ?? '' }}"
    data-initial-provincia="{{ $initialProvincia ?? '' }}"
    data-initial-distrito="{{ $initialDistrito ?? '' }}">
    <input type="hidden" name="departamento_id" value="{{ $initialDepartamento ?? '' }}" data-role="hidden-departamento">
    <input type="hidden" name="provincia_id" value="{{ $initialProvincia ?? '' }}" data-role="hidden-provincia">

    <div class="col-12 col-md-4">
        <div class="form-group-grobdi">
            <label for="{{ $departamentoInputId }}" class="grobdi-label">{{ $labelDepartamento }}:</label>
            <select id="{{ $departamentoInputId }}" class="grobdi-input" data-role="departamento" data-placeholder="Selecciona un {{ strtolower($labelDepartamento) }}">
                <option value="">Selecciona un {{ strtolower($labelDepartamento) }}</option>
            </select>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group-grobdi">
            <label for="{{ $provinciaInputId }}" class="grobdi-label">{{ $labelProvincia }}:</label>
            <select id="{{ $provinciaInputId }}" class="grobdi-input" data-role="provincia" data-placeholder="Selecciona una {{ strtolower($labelProvincia) }}">
                <option value="">Selecciona una {{ strtolower($labelProvincia) }}</option>
            </select>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group-grobdi">
            <label for="{{ $distritoInputId }}" class="grobdi-label">{{ $labelDistrito }}:</label>
            <select name="{{ $fieldName }}" id="{{ $distritoInputId }}" class="grobdi-input @error($errorField) is-invalid @enderror" data-role="distrito" data-placeholder="Selecciona un {{ strtolower($labelDistrito) }}" {{ $required ? 'required' : '' }}>
                <option value="">Selecciona un {{ strtolower($labelDistrito) }}</option>
            </select>
            @error($errorField)
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
