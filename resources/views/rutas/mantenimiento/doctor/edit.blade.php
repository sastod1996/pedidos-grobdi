@extends('adminlte::page')

@section('title', 'Centro de salud')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('doctor.edit')
@php
    $initialDistritoId = old('distrito_id', $doctor->distrito_id);
    $initialProvinciaId = old('provincia_id', optional($doctor->distrito)->provincia_id);
    $initialDepartamentoId = old('departamento_id', optional(optional($doctor->distrito)->provincia)->departamento_id);
@endphp
<div class="grobdi-header mt-3">
    <div class="grobdi-title">
        <div>
            <h2 class="mb-0">Editar Doctor</h2>
            <p class="mb-0">Actualiza la información del doctor seleccionado.</p>
        </div>
        <a class="btn-grobdi btn-outline-primary-grobdi btn-sm" href="{{ route('doctor.index') }}">
            <i class="fa fa-arrow-left"></i>
            Atrás
        </a>
    </div>
</div>

<form action="{{ route('doctor.update', $doctor->id) }}" method="POST" class="grobdi-form">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="form-group-grobdi">
                <label for="inputName" class="grobdi-label">Nombres:</label>
                <input
                    type="text"
                    name="name"
                    value="{{ $doctor->name }}"
                    class="grobdi-input @error('name') is-invalid @enderror"
                    id="inputName"
                    placeholder="Ingresar el nombre del doctor">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <div class="form-group-grobdi">
                <label for="cmp" class="grobdi-label">CMP:</label>
                <input
                    type="text"
                    name="cmp"
                    value="{{ $doctor->CMP }}"
                    class="grobdi-input @error('cmp') is-invalid @enderror"
                    id="cmp"
                    placeholder="Ingresar el nro de CMP del doctor">
                @error('cmp')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="phone" class="grobdi-label">Teléfono:</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ $doctor->phone }}"
                    class="grobdi-input @error('phone') is-invalid @enderror"
                    id="phone"
                    placeholder="Ingresar el número de teléfono del doctor">
                @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <x-grobdi.location-selector
                :departamentos-endpoint="route('ubicaciones.departamentos.index')"
                :provincias-endpoint="route('ubicaciones.departamentos.provincias', ['departamento' => '__departamento__'])"
                :distritos-endpoint="route('ubicaciones.provincias.distritos', ['provincia' => '__provincia__'])"
                :chain-endpoint="route('ubicaciones.distritos.chain', ['distrito' => '__distrito__'])"
                :selected-departamento="$initialDepartamentoId"
                :selected-provincia="$initialProvinciaId"
                :selected-distrito="$initialDistritoId"
                label-departamento="Departamento"
                label-provincia="Provincia"
                label-distrito="Distrito"
                name="distrito_id"
                required
                error-key="distrito_id"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="especialidad" class="grobdi-label">Especialidades:</label>
                <select class="grobdi-input @error('especialidad_id') is-invalid @enderror" name="especialidad_id" id="especialidad">
                    <option selected disabled>Seleccione una especialidad</option>
                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}" {{ $doctor->especialidad_id == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->name }}</option>
                    @endforeach
                </select>
                @error('especialidad_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="birthdate" class="grobdi-label">Fecha de nacimiento:</label>
                <input
                    type="date"
                    name="birthdate"
                    value="{{ $doctor->birthdate }}"
                    class="grobdi-input @error('birthdate') is-invalid @enderror"
                    id="birthdate"
                    placeholder="Ingresar su fecha de nacimiento">
                @error('birthdate')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="categoria" class="grobdi-label">Categoría Médico:</label>
                <select class="grobdi-input" name="categoria_medico" id="categoria">
                    <option disabled>Seleccione</option>
                    <option value="Empresa" {{ $doctor->categoria_medico == 'Empresa' ? 'selected' : '' }}>Empresa</option>
                    <option value="Visitador" {{ $doctor->categoria_medico == 'Visitador' ? 'selected' : '' }}>Visitador</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="tipo_medico" class="grobdi-label">Tipo Médico:</label>
                <select class="grobdi-input" name="tipo_medico" id="tipo_medico">
                    <option selected disabled>Seleccione</option>
                    @foreach (App\Models\Doctor::TIPOMEDICO as $tipo_medico)
                        <option value="{{ $tipo_medico }}" {{ $doctor->tipo_medico == $tipo_medico ? 'selected' : '' }}>{{ $tipo_medico }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="asignado_consultorio" class="grobdi-label">¿Asignado a consultorio?</label>
                <select class="grobdi-input" name="asignado_consultorio" id="asignado_consultorio">
                    <option selected disabled>Seleccione</option>
                    <option value="0" {{ $doctor->asignado_consultorio == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $doctor->asignado_consultorio == 1 ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="hijos" class="grobdi-label">¿Padre?</label>
                <select class="grobdi-input" name="songs" id="hijos">
                    <option disabled>Seleccione</option>
                    <option value="0" {{ $doctor->songs == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $doctor->songs == 1 ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="form-group-grobdi">
                <label for="search" class="grobdi-label">Centro de Salud:</label>
                <input
                    type="text"
                    id="search"
                    name="centrosalud_name"
                    placeholder="Buscar centro de salud..."
                    autocomplete="off"
                    value="{{ $doctor->centrosalud->name }}"
                    class="grobdi-input @error('centrosalud_id') is-invalid @enderror">
                <input type="hidden" id="centrosalud_id" name="centrosalud_id" value="{{ $doctor->centrosalud_id }}">
                <ul id="suggestions" class="list-unstyled mt-2" style="display: none;"></ul>
                @error('centrosalud_id')
                    <div class="invalid-feedback d-block">Seleccione un centro de salud, si no lo encuentra debe crearlo antes</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <div class="form-group-grobdi">
                <label for="recovery" class="grobdi-label">¿Es recuperación?</label>
                <select class="grobdi-input" name="recovery" id="recovery">
                    <option selected disabled>Seleccione</option>
                    <option value="0" {{ $doctor->recovery == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $doctor->recovery == 1 ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group-grobdi">
                <label class="grobdi-label">Días disponible:</label>
                <div class="grobdi-checkbox-group">
                    @foreach ($dias as $dia)
                        <label class="grobdi-checkbox" for="dia_{{ $dia->id }}">
                            <input
                                type="checkbox"
                                value="{{ $dia->id }}"
                                id="dia_{{ $dia->id }}"
                                name="dias[]"
                                {{ in_array($dia->id, $array_diasselect) ? 'checked' : '' }}>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-label">{{ $dia->name }}</span>
                        </label>
                        <div id="turno_{{ $dia->id }}" class="turno-container form-group-grobdi" style="display: none;">
                            <label for="turno_{{ $dia->id }}_select" class="grobdi-label">Selecciona el turno:</label>
                            <select name="turno_{{ $dia->id }}" id="turno_{{ $dia->id }}_select" class="grobdi-input">
                                <option value="0" selected>Turno Mañana</option>
                                <option value="1">Turno Tarde</option>
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group-grobdi">
                <label for="name_secretariat" class="grobdi-label">Secretaria:</label>
                <input
                    type="text"
                    name="name_secretariat"
                    value="{{ $doctor->name_secretariat }}"
                    class="grobdi-input @error('name_secretariat') is-invalid @enderror"
                    id="name_secretariat"
                    placeholder="Ingresar el nombre de la secretaria">
                @error('name_secretariat')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <div class="form-group-grobdi">
                <label for="phone_secretariat" class="grobdi-label">Teléfono secretaria:</label>
                <input
                    type="text"
                    name="phone_secretariat"
                    value="{{ $doctor->phone_secretariat }}"
                    class="grobdi-input @error('phone_secretariat') is-invalid @enderror"
                    id="phone_secretariat"
                    placeholder="Ingresar el número de teléfono de la secretaria">
                @error('phone_secretariat')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group-grobdi">
                <label for="observaciones" class="grobdi-label">Observaciones:</label>
                <input
                    type="text"
                    name="observations"
                    value="{{ $doctor->observations }}"
                    class="grobdi-input @error('observations') is-invalid @enderror"
                    id="observaciones"
                    placeholder="Ingresar las observaciones">
                @error('observations')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group-grobdi">
                <label for="categoria_id" class="grobdi-label">Categoría del doctor:</label>
                <select class="grobdi-input @error('categoria_id') is-invalid @enderror" name="categoria_id" id="categoria_id">
                    <option selected disabled>Seleccione la categoría</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ $doctor->categoriadoctor_id == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
                    @endforeach
                </select>
                @error('categoria_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

    <div class="form-actions justify-end">
        <button type="submit" class="btn-grobdi btn-success-grobdi">
            <i class="fa-solid fa-floppy-disk"></i>
            Guardar cambios
        </button>
    </div>
</form>
@endcan
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="{{ asset('js/components/location-selector.js') }}" defer></script>

<script>
    jQuery(function ($) {
        const toggleTurno = function (checkbox) {
            const diaId = checkbox.id.replace('dia_', '');
            const turnoContainer = $('#turno_' + diaId);

            if (checkbox.checked) {
                turnoContainer.show();
            } else {
                turnoContainer.hide();
                turnoContainer.find('select').val('0');
            }
        };

        $('input[type="checkbox"][name="dias[]"]').each(function () {
            toggleTurno(this);
        }).on('change', function () {
            toggleTurno(this);
        });

        $('#search').on('input', function () {
            const query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: '/centrosaludbuscar',
                    method: 'GET',
                    data: { term: query },
                    success: function (data) {
                        const $suggestions = $('#suggestions');
                        $suggestions.empty().show();
                        if (data.length > 0) {
                            data.forEach(function (centrosalud) {
                                $suggestions.append('<li data-id="' + centrosalud.id + '">' + centrosalud.text + '</li>');
                            });
                        } else {
                            $suggestions.hide();
                        }
                    }
                });
            } else {
                $('#suggestions').empty().hide();
            }
        });

        $(document).on('click', '#suggestions li', function () {
            const centrosaludName = $(this).text();
            const centrosaludId = $(this).data('id');
            $('#search').val(centrosaludName);
            $('#centrosalud_id').val(centrosaludId);
            $('#suggestions').empty().hide();
        });
    });
</script>
@stop
