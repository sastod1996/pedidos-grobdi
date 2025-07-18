@extends('adminlte::page')

@section('title', 'Centro de salud')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
<div class="card mt-2">
  <h2 class="card-header">Registrar Doctor</h2>
  <div class="card-body">
  
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ route('doctor.index') }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
  
    <form action="{{ route('doctor.store') }}" method="POST">
        @csrf
  
        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <label for="inputName" class="form-label"><strong>Nombres:</strong></label>
                <input 
                    type="text" 
                    name="name" 
                    value=""
                    class="form-control @error('name') is-invalid @enderror" 
                    id="inputName" 
                    placeholder="Ingresar el nombre del doctor">
                @error('name')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-2 col-sm-2 col-md-2">
                <label for="cmp" class="form-label"><strong>CMP:</strong></label>
                <input 
                    type="cmp" 
                    name="cmp" 
                    value="{{ old('cmp') }}"
                    class="form-control @error('cmp') is-invalid @enderror" 
                    id="cmp" 
                    placeholder="Ingresar el nro de CMP del doctor"
                    >
                @error('cmp')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="phone" class="form-label"><strong>Telefono:</strong></label>
                <input 
                    type="text" 
                    name="phone" 
                    value="{{ old('phone') }}"
                    class="form-control @error('phone') is-invalid @enderror" 
                    id="phone" 
                    placeholder="Ingresar el número de telefono del doctor">
                @error('phone')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="distrito_id" class="form-label"><strong>Distrito:</strong></label>
                <select class="form-select @error('distrito_id') is-invalid @enderror" aria-label="distrito_id" name="distrito_id" >
                    <option selected disabled>Seleccione el distrito</option>
                    @foreach ($distritos as $distrito)
                        <option value="{{ $distrito->id }}">{{ $distrito->name }}</option>
                    @endforeach
                </select>
                @error('distrito_id')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="especialidad" class="form-label"><strong>Especialidades:</strong></label>
                <select class="form-select @error('especialidad_id') is-invalid @enderror" aria-label="selecciona un especialidad" name="especialidad_id">
                    <option selected disabled>Seleccione una especialidad</option>
                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}">{{ $especialidad->name }}</option>
                    @endforeach
                </select>
                @error('especialidad_id')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="birthdate" class="form-label"><strong>Fecha de nacimiento:</strong></label>
                <input 
                    type="date" 
                    name="birthdate" 
                    value="{{ old('birthdate') }}"
                    class="form-control @error('birthdate') is-invalid @enderror" 
                    id="birthdate" 
                    placeholder="Ingresar su fecha de nacimiento">
                @error('birthdate')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="categoria" class="form-label"><strong>Categoría Médico:</strong></label>
                <select class="form-select" aria-label="categoria" name="categoria_medico">
                    <option selected disabled>Seleccione</option>
                    <option value="empresa">Empresa</option>
                    <option value="visitador">Visitador</option>
                </select>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="tipo_medico" class="form-label"><strong>Tipo Médico:</strong></label>
                <select class="form-select" aria-label="tipo_medico" name="tipo_medico">
                    <option selected disabled>Seleccione</option>
                    @foreach ( App\Models\Doctor::TIPOMEDICO as $tipo_medico)
                    <option value="{{ $tipo_medico }}" {{ old('tipo_medico') == $tipo_medico ? 'selected' : '' }}>{{$tipo_medico}}</option>
                    
                    @endforeach
                </select>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="tipo_medico" class="form-label"><strong>¿Asignado a consultorio?</strong></label>
                <select class="form-select" aria-label="asignado_consultorio" name="asignado_consultorio">
                    <option selected disabled>Seleccione</option>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="hijos" class="form-label"><strong>¿Padre?</strong></label>
                <select class="form-select" aria-label="hijos" name="songs">
                    <option selected disabled>Seleccione</option>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
            <label for="hijos" class="form-label"><strong>Centro de Salud</strong></label>
                <input type="text" 
                id="search" 
                name="centrosalud_name" 
                placeholder="Buscar centro de salud..." 
                autocomplete="off" 
                class="form-control @error('centrosalud_id') is-invalid @enderror">
                <input type="hidden" id="centrosalud_id" name="centrosalud_id">
                <ul id="suggestions" style="display: none;"></ul>
                @error('centrosalud_id')
                    <div class="form-text text-danger">Seleccione un centro de salud, si no lo encuentra debe crearlo antes</div>
                @enderror
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="tipo_medico" class="form-label"><strong>Días disponible:</strong></label>
                @foreach ($dias as $dia)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $dia->id }}" id="dia_{{ $dia->id }}" name="dias[]">
                        <label class="form-check-label" for="dia_{{ $dia->id }}">
                            {{ $dia->name }}
                        </label>
                    </div>
                    <div id="turno_{{ $dia->id }}" class="turno-container" style="display: none;">
                        <label for="turno_{{ $dia->id }}">Selecciona el turno:</label>
                        <select name="turno_{{ $dia->id }}" id="turno_{{ $dia->id }}">
                            <option value="" selected disabled>Seleccione</option>
                            <option value="0">Turno Mañana</option>
                            <option value="1">Turno Tarde</option>
                        </select>
                    </div>
                @endforeach
                <br>
            </div>
            <br>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <label for="name_secretariat" class="form-label"><strong>Secretaria:</strong></label>
                <input 
                    type="text" 
                    name="name_secretariat" 
                    value=""
                    class="form-control @error('name_secretariat') is-invalid @enderror" 
                    id="name_secretariat" 
                    placeholder="Ingresar el nombre de la secretaria">
                @error('name_secretariat')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3">
                <label for="phone_secretariat" class="form-label"><strong>Telefono secretaria:</strong></label>
                <input 
                    type="text" 
                    name="phone_secretariat" 
                    value=""
                    class="form-control @error('phone_secretariat') is-invalid @enderror" 
                    id="phone_secretariat" 
                    placeholder="Ingresar el número de telefono de la secretaria">
                @error('phone_secretariat')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <label for="observaciones" class="form-label"><strong>observaciones:</strong></label>
                <input 
                    type="text" 
                    name="observations" 
                    value=""
                    class="form-control @error('observations') is-invalid @enderror" 
                    id="observaciones" 
                    placeholder="Ingresar las observaciones">
                @error('observations')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Registrar</button>
    </form>
  
  </div>
</div>

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        // Función para mostrar el combobox correspondiente cuando un checkbox es marcado
        $('input[type="checkbox"]').change(function() {
            var diaId = $(this).attr('id').replace('dia_', ''); // Obtiene el día seleccionado
            var turnoContainer = $('#turno_' + diaId);

            if ($(this).is(':checked')) {
                turnoContainer.show();  // Muestra el combobox para ese día
            } else {
                turnoContainer.hide();  // Oculta el combobox para ese día
                $('#turno_' + diaId + ' select').val('');  // Resetea el valor del combobox
            }
        });
    });
    $(document).ready(function() {
        $('#search').on('input', function() {
            var query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: '/centrosaludbuscar',
                    method: 'GET',
                    data: { term: query },
                    success: function(data) {
                        $('#suggestions').empty().show();
                        if (data.length > 0) {
                            data.forEach(function(centrosalud) {
                                console.log(centrosalud);
                                $('#suggestions').append('<li data-id="' + centrosalud.id + '">' + centrosalud.name + '</li>');
                            });
                        } else {
                            $('#suggestions').hide();
                        }
                    }
                });
            } else {
                $('#suggestions').empty().hide();
            }
        });

        // Cuando el usuario hace clic en una sugerencia
        $(document).on('click', '#suggestions li', function() {
            var centrosaludName = $(this).text();
            var centrosaludId = $(this).data('id');
            $('#search').val(centrosaludName);   // Rellenamos el campo de búsqueda con el nombre del centrosalud
            $('#centrosalud_id').val(centrosaludId);  // Rellenamos el campo oculto con el ID del centrosalud
            $('#suggestions').empty().hide();   // Ocultamos las sugerencias
        });
    });
</script>
@stop