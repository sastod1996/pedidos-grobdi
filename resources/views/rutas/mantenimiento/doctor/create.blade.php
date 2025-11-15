@extends('adminlte::page')

@section('title', 'Centro de salud')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('doctor.create')
<div class="grobdi-header mt-3">
    <div class="grobdi-title">
        <div>
            <h2 class="mb-0">Registrar Doctor</h2>
            <p class="mb-0">Completa los datos para crear un nuevo registro de doctor.</p>
        </div>
        <a class="btn-grobdi btn-outline-primary-grobdi btn-sm" href="{{ route('doctor.index') }}">
            <i class="fa fa-arrow-left"></i>
            Atrás
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert-grobdi alert-danger-grobdi">
        <div class="alert-content" role="alert">
            <strong class="d-block mb-2">Revisa los campos marcados:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('doctor.store') }}" method="POST" class="grobdi-form">
    @csrf
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
            />
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <x-grobdi.form.input
                label="CMP:"
                name="cmp"
                id="cmp"
                placeholder="Ingresar el nro de CMP del doctor"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.input
                label="Teléfono:"
                name="phone"
                id="phone"
                placeholder="Ingresar el número de teléfono del doctor"
            />
        </div>
        <div class="col-12">
            <x-grobdi.location-selector
                :departamentos-endpoint="route('ubicaciones.departamentos.index')"
                :provincias-endpoint="route('ubicaciones.departamentos.provincias', ['departamento' => '__departamento__'])"
                :distritos-endpoint="route('ubicaciones.provincias.distritos', ['provincia' => '__provincia__'])"
                :chain-endpoint="route('ubicaciones.distritos.chain', ['distrito' => '__distrito__'])"
                :selected-distrito="old('distrito_id')"
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
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="Categoría Médico:"
                name="categoria_medico"
                id="categoria"
                placeholder="Seleccione"
                :options="[
                    ['value' => 'empresa', 'label' => 'Empresa'],
                    ['value' => 'visitador', 'label' => 'Visitador'],
                ]"
            />
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <x-grobdi.form.select
                label="Tipo Médico:"
                name="tipo_medico"
                id="tipo_medico"
                placeholder="Seleccione"
                :options="$tipoMedicoOptions"
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
            />
        </div>
        <div class="col-12 col-lg-6">
            <x-grobdi.form.select
                label="Centro de Salud:"
                name="centrosalud_id"
                id="centrosalud_id"
                placeholder="Buscar centro de salud"
                :options="[]"
                :input-attrs="['style' => 'width: 100%;']"
                error-message="Seleccione un centro de salud, si no lo encuentra debe crearlo antes"
            />
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
            />
        </div>
        <div class="col-12">
            <div class="form-group-grobdi">
                <label class="grobdi-label">Días disponible:</label>
                <div class="grobdi-checkbox-group">
                    @foreach ($dias as $dia)
                        <label class="grobdi-checkbox" for="dia_{{ $dia->id }}">
                            <input class="" type="checkbox" value="{{ $dia->id }}" id="dia_{{ $dia->id }}" name="dias[]" {{ collect(old('dias', []))->contains($dia->id) ? 'checked' : '' }}>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-label">{{ $dia->name }}</span>
                        </label>
                        <div id="turno_{{ $dia->id }}" class="turno-container form-group-grobdi" style="display: none;">
                            <label for="turno_{{ $dia->id }}_select" class="grobdi-label">Selecciona el turno:</label>
                            <select name="turno_{{ $dia->id }}" id="turno_{{ $dia->id }}_select" class="grobdi-input">
                                <option value="" selected disabled>Seleccione</option>
                                <option value="0">Turno Mañana</option>
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
            />
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <x-grobdi.form.input
                label="Teléfono secretaria:"
                name="phone_secretariat"
                id="phone_secretariat"
                placeholder="Ingresar el número de teléfono de la secretaria"
            />
        </div>
        <div class="col-12">
            <x-grobdi.form.input
                label="Observaciones:"
                name="observations"
                id="observaciones"
                placeholder="Ingresar las observaciones"
            />
        </div>
        <div class="col-12 col-md-6">
            <x-grobdi.form.select
                label="Categoría del doctor:"
                name="categoria_id"
                id="categoria_id"
                placeholder="Seleccione la categoría"
                :options="$categoriaDoctorOptions"
                error-key="categoria_id"
            />
        </div>
    </div>

    <div class="form-actions justify-end">
        <button type="submit" class="btn-grobdi btn-success-grobdi">
            <i class="fa-solid fa-floppy-disk"></i>
            Registrar
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
                turnoContainer.find('select').val('');
            }
        };

        $('input[type="checkbox"][name="dias[]"]').each(function () {
            toggleTurno(this);
        }).on('change', function () {
            toggleTurno(this);
        });

        $('#centrosalud_id').select2({
            placeholder: 'Buscar centro de salud',
            minimumInputLength: 2,
            ajax: {
                url: "{{ route('centrosalud.buscar') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { term: params.term };
                },
                processResults: function (data, params) {
                    const results = data.map(item => ({
                        id: item.id,
                        text: item.text
                    }));

                    if (params.term && results.length === 0) {
                        results.push({
                            id: 'nuevo:' + params.term,
                            text: '➕ Agregar nuevo centro: "' + params.term + '"'
                        });
                    }

                    return { results: results };
                },
                cache: true
            },
            language: 'es'
        });

        $('#centrosalud_id').on('select2:select', function (e) {
            const data = e.params.data;

            if (String(data.id).startsWith('nuevo:')) {
                const nombreNuevo = String(data.id).replace('nuevo:', '');

                if (!confirm(`¿Deseas agregar "${nombreNuevo}" como nuevo centro de salud?`)) {
                    $('#centrosalud_id').val(null).trigger('change');
                    return;
                }

                $.ajax({
                    url: "{{ route('centrosalud.crearflorante') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        name: nombreNuevo,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        const option = new Option(res.text, res.id, true, true);
                        $('#centrosalud_id').append(option).trigger('change');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            alert(Object.values(xhr.responseJSON.errors).flat().join('\n'));
                        } else {
                            alert('Error al crear el centro de salud.');
                        }
                        $('#centrosalud_id').val(null).trigger('change');
                    }
                });
            }
        });
    });
</script>
@stop
