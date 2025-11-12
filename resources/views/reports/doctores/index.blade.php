@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-user-md text-danger mr-1"></i>Reporte Comercial - Doctores</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Reporte de Doctores</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @can('reports.doctors')
    <div class="row">
        <div class="col-12">
            <div class="card card-danger card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="doctorsTab" role="tablist">
                        <li class="pt-2 px-3">
                            <h3 class="card-title">Fluctuación de Ventas</h3>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="doctor-tab" data-toggle="pill" href="#doctor" role="tab"
                                data-target="#doctor" aria-controls="doctor" aria-selected="true">
                                <i class="fas fa-user-md"></i> Doctor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tipo-doctor-tab" data-toggle="pill" href="#tipo-doctor" role="tab"
                                aria-controls="tipo-doctor" data-target="#tipo-doctor" aria-selected="false">
                                <i class="fas fa-stethoscope"></i> Tipo Doctor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="seguimiento-tab" data-toggle="pill" href="#seguimiento" role="tab"
                                aria-controls="seguimiento" data-target="#seguimiento" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Seguimiento
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="tabs-tabContent">
                        <div class="tab-pane fade show active" id="doctor" role="tabpanel" aria-labelledby="doctor-tab">
                            @include('reports.doctores.partials.doctor')
                        </div>
                        <div class="tab-pane fade" id="tipo-doctor" role="tabpanel" aria-labelledby="tipo-doctor-tab">
                            @include('reports.doctores.partials.tipo-doctor')
                        </div>
                        <div class="tab-pane fade" id="seguimiento" role="tabpanel" aria-labelledby="seguimiento-tab">
                            @include('reports.doctores.partials.seguimiento')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
@stop

@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)
@section('plugins.DatePicker', true)

@section('js')
    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script src="{{ asset('js/table-helpers.js') }}"></script>
    <script src="{{ asset('js/get-money-format.js') }}"></script>
    <script src="{{ asset('js/generate-hsl-color.js') }}"></script>
    <script src="{{ asset('js/autocomplete-input.js') }}"></script>

    @stack('partial-js')

@endsection
