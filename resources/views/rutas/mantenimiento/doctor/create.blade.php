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

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="form-group-grobdi">
                <label for="inputName" class="grobdi-label">Nombres:</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
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
                    value="{{ old('cmp') }}"
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
                    value="{{ old('phone') }}"
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
            <div class="form-group-grobdi">
                <label for="especialidad" class="grobdi-label">Especialidades:</label>
                <select class="grobdi-input @error('especialidad_id') is-invalid @enderror" name="especialidad_id" id="especialidad">
                    <option selected disabled>Seleccione una especialidad</option>
                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}" {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->name }}</option>
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
                    value="{{ old('birthdate') }}"
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
                    <option selected disabled>Seleccione</option>
                    <option value="empresa" {{ old('categoria_medico') == 'empresa' ? 'selected' : '' }}>Empresa</option>
                    <option value="visitador" {{ old('categoria_medico') == 'visitador' ? 'selected' : '' }}>Visitador</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="tipo_medico" class="grobdi-label">Tipo Médico:</label>
                <select class="grobdi-input" name="tipo_medico" id="tipo_medico">
                    <option selected disabled>Seleccione</option>
                    @foreach (App\Models\Doctor::TIPOMEDICO as $tipo_medico)
                        <option value="{{ $tipo_medico }}" {{ old('tipo_medico') == $tipo_medico ? 'selected' : '' }}>{{ $tipo_medico }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="asignado_consultorio" class="grobdi-label">¿Asignado a consultorio?</label>
                <select class="grobdi-input" name="asignado_consultorio" id="asignado_consultorio">
                    <option selected disabled>Seleccione</option>
                    <option value="0" {{ old('asignado_consultorio') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('asignado_consultorio') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group-grobdi">
                <label for="hijos" class="grobdi-label">¿Padre?</label>
                <select class="grobdi-input" name="songs" id="hijos">
                    <option selected disabled>Seleccione</option>
                    <option value="0" {{ old('songs') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('songs') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="form-group-grobdi">
                <label for="centrosalud_id" class="grobdi-label">Centro de Salud:</label>
                <select id="centrosalud_id" name="centrosalud_id" class="grobdi-input" style="width: 100%;"></select>
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
                    <option value="0" {{ old('recovery') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('recovery') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
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
            <div class="form-group-grobdi">
                <label for="name_secretariat" class="grobdi-label">Secretaria:</label>
                <input
                    type="text"
                    name="name_secretariat"
                    value="{{ old('name_secretariat') }}"
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
                    value="{{ old('phone_secretariat') }}"
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
                    value="{{ old('observations') }}"
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
                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
                    @endforeach
                </select>
                @error('categoria_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
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
