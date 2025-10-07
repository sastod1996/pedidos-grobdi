@extends('adminlte::page')

@section('title', 'Reporte de Visitadoras')

@section('content_header')
<h1><i class="fas fa-user-friends text-success"></i> Reporte de Visitadoras</h1>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('content')
<div class="container-fluid">
    <div class="card bg-light">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="visitadorasTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="rutas-tab" data-toggle="tab" data-target="#rutas" type="button" role="tab">
                        <i class="fas fa-route"></i> Rutas
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="visitadorasTabsContent">
                @include('reports.visitadoras.index')
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Chartjs', true)

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Lógica dinámica migrada al partial rutas.blade.php
</script>
@endsection