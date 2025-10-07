@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Reporte Comercial por Doctores</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Reporte Comercial - Doctores</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-danger card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                    <li class="pt-2 px-3">
                        <h3 class="card-title">Fluctuación de Ventas</h3>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-general-tab" data-toggle="pill" href="#tabs-general" role="tab" aria-controls="tabs-general" aria-selected="false">General</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-por-visitadora-tab" data-toggle="pill" href="#tabs-por-visitadora" role="tab" aria-controls="tabs-por-visitadora" aria-selected="true">Por visitadora</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-por-producto-tab" data-toggle="pill" href="#tabs-por-producto" role="tab" aria-controls="tabs-por-producto" aria-selected="false">Por producto</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="tabs-tabContent">
                    <div class="tab-pane fade" id="tabs-general" role="tabpanel" aria-labelledby="tabs-general-tab">
                        General
                    </div>
                    <div class="tab-pane fade show active" id="tabs-por-visitadora" role="tabpanel" aria-labelledby="tabs-por-visitadora-tab">
<<<<<<< HEAD
                        @include('reports.doctores.partials.porDoctor')
=======
                        @include('reports.doctores.partials.doctor')
>>>>>>> f76f4ac7a11c11334cc0a0e9b770a16c887d9683
                    </div>
                    <div class="tab-pane fade" id="tabs-por-producto" role="tabpanel" aria-labelledby="tabs-por-producto-tab">
                        Producto
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop