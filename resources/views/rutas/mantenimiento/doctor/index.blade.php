@extends('adminlte::page')

@section('title', 'Doctores')

@section('content_header')
                <h5 class="mb-0 text-primary"><i class="fa fa-users"></i> Gestión de Doctores</h5>

@stop

@section('content')
<div class="card mt-2">
    <div class="card-header bg-light border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a class="btn btn-success btn-sm" href="{{ route('doctor.create') }}" data-bs-toggle="tooltip" title="Crear un nuevo doctor manualmente">
                    <i class="fa fa-plus-circle"></i> Registrar Nuevo
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#itemModal" data-bs-toggle="tooltip" title="Importar doctores desde archivo Excel">
                    <i class="fa fa-upload"></i> Importar Excel
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        @session('success')
            <div class="alert alert-success" role="alert"> {{ $value }} </div>
        @endsession
        @session('danger')
            <div class="alert alert-danger" role="alert"> {{ $value }} </div>
        @endsession
        @error('archivo')
        <p style="color: red;">{{ $message }}</p>
        @enderror

        <!-- Card de Filtros -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('doctor.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label"><i class="fa fa-search"></i> Buscar por Nombre</label>
                        <input type="text" name="search" id="search" placeholder="Buscar..." value="{{ $search }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label"><i class="fa fa-calendar-alt"></i> Fecha de Creación (Inicio)</label>
                        <input type="text" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control flatpickr">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label"><i class="fa fa-calendar-alt"></i> Fecha de Creación (Fin)</label>
                        <input type="text" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control flatpickr">
                    </div>
                    <div class="col-md-6">
                        <label for="tipo_medico" class="form-label"><i class="fa fa-user-md"></i> Tipo de Médico</label>
                        <select name="tipo_medico" id="tipo_medico" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tiposMedico as $tipo)
                                <option value="{{ $tipo }}" {{ $tipoMedico == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="distrito_id" class="form-label"><i class="fa fa-map-marker-alt"></i> Distrito</label>
                        <select name="distrito_id" id="distrito_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($distritos as $distrito)
                                <option value="{{ $distrito->id }}" {{ $distritoId == $distrito->id ? 'selected' : '' }}>{{ $distrito->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        <a href="{{ route('doctor.index') }}" class="btn btn-secondary"><i class="fa fa-eraser"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body table-responsive p-0" style="height: 800px;">
            <table class="table table-head-fixed text-nowrap display" id="miTabla">
                <thead>

                    <tr>
                        <th>
                            <a href="{{ route('doctor.index', ['sort_by' => 'name', 'direction' => $ordenarPor == 'name' && $direccion == 'asc' ? 'desc' : 'asc']) }}">
                                Nombre 
                                @if ($ordenarPor == 'name')
                                    {{ $direccion == 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th>CMP</th>
                        <th>Cat.</th>
                        <th>Telefono</th>
                        <th>Especialidad</th>
                        <th>Centro de salud</th>
                        <th>Distrito</th>
                        <th>Tipo Medico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
    
                <tbody>
                @forelse ($doctores as $doctor)
                    <tr class={{ $doctor->state == 0 ? 'table-danger': ''}}>
                        <td>{{ $doctor->name }} {{ $doctor->first_lastname }} {{ $doctor->second_lastname }}</td>
                        <td>{{ $doctor->CMP }}</td>
                        <td>{{ $doctor->categoriadoctor->name }}</td>
                        <td>{{ $doctor->phone }}</td>
                        <td>{{ $doctor->especialidad->name }}</td>
                        <td>{{ $doctor->centrosalud->name }}</td>
                        <td>{{ $doctor->distrito? $doctor->distrito->name:"" }}</td>
                        <td>{{ $doctor->tipo_medico }}</td>
                        <td>
                            <form action="{{ route('doctor.destroy',$doctor->id) }}" method="POST">
                                <a class="btn btn-primary btn-xs" href="{{ route('doctor.edit',$doctor->id) }}"><i class="fa-solid fa-pen-to-square"></i> Actualizar</a>
                                @csrf
                                @method('DELETE')
                                @if($doctor->state == 1)
                                    <button type="submit" class="btn btn-danger btn-xs">Inhabilitar</button>
                                @else
                                    <button type="submit" class="btn btn-success btn-xs">Habilitar</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">No hay información que mostrar</td>
                    </tr>
                @endforelse
                </tbody>
    
            </table>
            {!! $doctores->appends(request()->except('page'))->links() !!}
        </div>
    </div>
</div>
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Cargar Datos de Doctores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('doctor.cargadata') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <label for="doctor_excel" class="form-label"><strong>Cargar target Doctores:</strong></label>
                        <input 
                            type="file" 
                            name="archivo" 
                            class="form-control"
                            accept=".xlsx, .csv,.xls" 
                            id="doctor_excel"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-success">Importar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.flatpickr', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection