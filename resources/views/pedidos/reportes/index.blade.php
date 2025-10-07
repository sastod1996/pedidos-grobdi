@extends('adminlte::page')

@section('title', 'Hoja de ruta de motorizados')

@section('content_header')
@stop

@section('content')

<div class="card card-dark mt-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Exportar - Hoja de Ruta de: Motorizado</h2>
            <a class="btn btn-outline-danger" href="{{ route("cargarpedidos.index") }}"><i class="fas fa-door-open"></i>
                <span class="d-none d-md-inline">Regresar</span>
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('motorizado.exportHojaDeRuta') }}" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-12">
                    <label for="requestedDate">Fecha solicitada:</label>
                    <input type="date" class="form-control datetimepicker-input" id="requestedDate" name="requestedDate" required>
                </div>

                <div class="form-group col-12">
                    <label for="motorizado_id">Motorizado</label>
                    <select name="motorizado_id" class="custom-select" required>
                        <option selected disabled>Seleccione el motorizado deseado</option>
                        @foreach ($motorizados as $motorizado)
                        <option value="{{ $motorizado->id }}">{{ $motorizado->name }} - {{ $motorizado->zone->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-success w-100" type="submit"><i class="fa fa-file-excel mr-1"></i>Descargar Excel</button>
        </form>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    @if(session('error'))
    toastr.error("{{ session('error') }}", "Error");
    @endif


    $('#requestedDate').on('click', function() {
        this.showPicker?.();
    });
</script>
@stop