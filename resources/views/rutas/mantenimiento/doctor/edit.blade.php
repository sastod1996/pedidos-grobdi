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
    @php
        $especialidadOptions = $especialidades->map(fn ($especialidad) => [
            'value' => $especialidad->id,
            'label' => $especialidad->name,
        ])->toArray();

        $categoriaDoctorOptions = $categorias->map(fn ($categoria) => [
            'value' => $categoria->id,
            'label' => $categoria->name,
        ])->toArray();

        $tipoMedicoOptions = collect(App\Models\Doctor::TIPOMEDICO)->map(fn ($tipo) => [
            'value' => $tipo,
            'label' => $tipo,
        ])->toArray();
    @endphp

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-grobdi.form.input
                label="Nombres:"
                name="name"
                id="inputName"
                placeholder="Ingresar el nombre del doctor"
                :value="$doctor->name"
            />
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <x-grobdi.form.input
                label="CMP:"
                name="cmp"
                id="cmp"
                placeholder="Ingresar el nro de CMP del doctor"
                :value="$doctor->CMP"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.input
                label="Teléfono:"
                name="phone"
                id="phone"
                placeholder="Ingresar el número de teléfono del doctor"
                :value="$doctor->phone"
            />
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
            <x-grobdi.form.select
                label="Especialidades:"
                name="especialidad_id"
                id="especialidad"
                placeholder="Seleccione una especialidad"
                :options="$especialidadOptions"
                :value="$doctor->especialidad_id"
                error-key="especialidad_id"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.input
                label="Fecha de nacimiento:"
                name="birthdate"
                id="birthdate"
                type="date"
                placeholder="Ingresar su fecha de nacimiento"
                :value="$doctor->birthdate"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="Categoría Médico:"
                name="categoria_medico"
                id="categoria"
                placeholder="Seleccione"
                :options="[
                    ['value' => 'Empresa', 'label' => 'Empresa'],
                    ['value' => 'Visitador', 'label' => 'Visitador'],
                ]"
                :value="$doctor->categoria_medico"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="Tipo Médico:"
                name="tipo_medico"
                id="tipo_medico"
                placeholder="Seleccione"
                :options="$tipoMedicoOptions"
                :value="$doctor->tipo_medico"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="¿Asignado a consultorio?"
                name="asignado_consultorio"
                id="asignado_consultorio"
                placeholder="Seleccione"
                :options="[
                    ['value' => '0', 'label' => 'No'],
                    ['value' => '1', 'label' => 'Sí'],
                ]"
                :value="(string) $doctor->asignado_consultorio"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="¿Padre?"
                name="songs"
                id="hijos"
                placeholder="Seleccione"
                :options="[
                    ['value' => '0', 'label' => 'No'],
                    ['value' => '1', 'label' => 'Sí'],
                ]"
                :value="(string) $doctor->songs"
            />
        </div>
        <div class="col-12 col-lg-6">
            <x-grobdi.form.input
                label="Centro de Salud:"
                name="centrosalud_name"
                id="search"
                placeholder="Buscar centro de salud..."
                autocomplete="off"
                error-key="centrosalud_id"
                error-message="Seleccione un centro de salud, si no lo encuentra debe crearlo antes"
                :value="$doctor->centrosalud->name"
            >
                <input type="hidden" id="centrosalud_id" name="centrosalud_id" value="{{ old('centrosalud_id', $doctor->centrosalud_id) }}">
                <ul id="suggestions" class="list-unstyled mt-2" style="display: none;"></ul>
            </x-grobdi.form.input>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <x-grobdi.form.select
                label="¿Es recuperación?"
                name="recovery"
                id="recovery"
                placeholder="Seleccione"
                :options="[
                    ['value' => '0', 'label' => 'No'],
                    ['value' => '1', 'label' => 'Sí'],
                ]"
                :value="(string) $doctor->recovery"
            />
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
            <x-grobdi.form.input
                label="Secretaria:"
                name="name_secretariat"
                id="name_secretariat"
                placeholder="Ingresar el nombre de la secretaria"
                :value="$doctor->name_secretariat"
            />
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <x-grobdi.form.input
                label="Teléfono secretaria:"
                name="phone_secretariat"
                id="phone_secretariat"
                placeholder="Ingresar el número de teléfono de la secretaria"
                :value="$doctor->phone_secretariat"
            />
        </div>
        <div class="col-12">
            <x-grobdi.form.input
                label="Observaciones:"
                name="observations"
                id="observaciones"
                placeholder="Ingresar las observaciones"
                :value="$doctor->observations"
            />
        </div>
        <div class="col-12 col-md-6">
            <x-grobdi.form.select
                label="Categoría del doctor:"
                name="categoria_id"
                id="categoria_id"
                placeholder="Seleccione la categoría"
                :options="$categoriaDoctorOptions"
                :value="$doctor->categoriadoctor_id"
                error-key="categoria_id"
            />
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
