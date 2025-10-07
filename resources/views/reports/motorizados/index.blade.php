@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-motorcycle mr-1 text-danger"></i> Indicadores de Motorizados</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Reporte Motorizados</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Detalle adicional -->
    <div class="row">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h5 class="m-0"><i class="fas fa-chart-bar mr-2"></i>Resumen de Pedidos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-md-4 order-2 order-md-1">
                            <div class="small-box bg-dark">
                                <div class="inner">
                                    <h3>142</h3>
                                    <p>Pedidos Programados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day" style="transform: none;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 order-1 order-md-2">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 style="cursor: default; position: relative;">
                                        <span class="total">
                                            128
                                        </span>
                                        <span class="percentage">
                                            90.1%
                                        </span>
                                    </h3>
                                    <p>Pedidos Entregados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle" style="transform: none;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 order-3">
                            <div class="small-box bg-dark">
                                <div class="inner">
                                    <h3 style="cursor: default; position: relative;">
                                        <span class="total">
                                            14
                                        </span>
                                        <span class="percentage">
                                            10.9%
                                        </span>
                                    </h3>
                                    <p>Pedidos Reprogramados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-redo-alt" style="transform: none;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h6 class="m-0">
                                        Tabla de Detalles Semanal por Mes
                                    </h6>
                                    {{-- <div class="card-tools">
                                        <div class="input-group input-group-sm" style="width: 150px;">
                                            <input type="text" name="table_search" class="form-control float-right"
                                                placeholder="Buscar">

                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-default">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                                <div class="card-body table-responsive p-0" style="height: 600px;">
                                    <table class="table table-dark table-head-fixed text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>
                                                    Mes
                                                </th>
                                                <th class="text-center align-content-center" style="width: 15%">
                                                    Programados
                                                </th>
                                                <th class="text-center align-content-center" style="width: 15%">
                                                    Entregados
                                                </th>
                                                <th class="text-center align-content-center" style="width: 20%">
                                                    % de Cumplimiento
                                                </th>
                                                <th class="text-center align-content-center" style="width: 15%">
                                                    Reprogramados
                                                </th>
                                                <th class="text-center align-content-center" style="width: 20%">
                                                    % de Reprogramados
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < 6; $i++)
                                                <tr data-toggle="collapse" data-target="#mes{{ $i }}"
                                                    class="accordion-toggle" style="cursor: pointer;">
                                                    <td class="align-content-center">
                                                        <strong>Mes {{ $i }}</strong>
                                                    </td>
                                                    <td class="text-center align-content-center">120</td>
                                                    <td class="text-center align-content-center">100</td>
                                                    <td class="text-center align-content-center">83%</td>
                                                    <td class="text-center align-content-center">10</td>
                                                    <td class="text-center align-content-center">8%</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="p-0">
                                                        <div class="collapse" id="mes{{ $i }}">
                                                            <table
                                                                class="table table-sm table-striped table-light text-dark text-nowrap">
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="align-content-center" style="width: 15%">
                                                                            01 - 07
                                                                        </td>
                                                                        <td class="align-content-center text-center"
                                                                            style="width: 15%">30</td>
                                                                        <td class="align-content-center text-center"
                                                                            style="width: 15%">25</td>
                                                                        <td class="align-content-center text-center"
                                                                            style="width: 20%">83%
                                                                        </td>
                                                                        <td class="align-content-center text-center"
                                                                            style="width: 15%">2</td>
                                                                        <td class="align-content-center text-center"
                                                                            style="width: 20%">7%</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="align-content-center">08 - 14</td>
                                                                        <td class="align-content-center text-center">28</td>
                                                                        <td class="align-content-center text-center">22</td>
                                                                        <td class="align-content-center text-center">78%
                                                                        </td>
                                                                        <td class="align-content-center text-center">3</td>
                                                                        <td class="align-content-center text-center">10%
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="align-content-center">15 - 21</td>
                                                                        <td class="align-content-center text-center">32</td>
                                                                        <td class="align-content-center text-center">29</td>
                                                                        <td class="align-content-center text-center">90%
                                                                        </td>
                                                                        <td class="align-content-center text-center">1</td>
                                                                        <td class="align-content-center text-center">3%</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="align-content-center">22 - 31</td>
                                                                        <td class="align-content-center text-center">30
                                                                        </td>
                                                                        <td class="align-content-center text-center">24
                                                                        </td>
                                                                        <td class="align-content-center text-center">80%
                                                                        </td>
                                                                        <td class="align-content-center text-center">4</td>
                                                                        <td class="align-content-center text-center">13%
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="card card-danger card-outline">
                                <div class="card-header border-bottom bo">
                                    <h3 class="card-title"><i class="fas fa-truck mr-2"></i>Programación vs. Entregas</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success"><i
                                                        class="fas fa-calendar-check"></i>
                                                    Programados</span>
                                                <h5 class="description-header ">142</h5>
                                                <span class="description-text">Pedidos del día</span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <div class="description-block">
                                                <span class="description-percentage text-success"><i
                                                        class="fas fa-check-circle"></i>
                                                    Entregados</span>
                                                <h5 class="description-header ">128</h5>
                                                <span class="description-text">Pedidos efectivos</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="progress-group">
                                                <span class="float-right"><b>90.1%</b> Cumplimiento</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" style="width: 90%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pedidos Reprogramados -->
                        <div class="col-lg-6 col-12">
                            <div class="card card-danger card-outline">
                                <div class="card-header border-bottom bo">
                                    <h3 class="card-title"><i class="fas fa-redo mr-2"></i>Pedidos Reprogramados</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-danger"><i
                                                        class="fas fa-exclamation-triangle"></i>
                                                    Reprogramados</span>
                                                <h5 class="description-header ">14</h5>
                                                <span class="description-text">Pedidos reprogramados</span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <div class="description-block">
                                                <span class="description-percentage text-danger"><i
                                                        class="fas fa-percentage"></i>
                                                    Porcentaje</span>
                                                <h5 class="description-header ">9.9%</h5>
                                                <span class="description-text">del total programado</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <h6><i class="fas fa-list mr-2"></i>Motivos de Reprogramación</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-user-times text-warning mr-2"></i> Cliente no
                                                    disponible:
                                                    <strong>6</strong>
                                                </li>
                                                <li><i class="fas fa-map-marker-alt text-warning mr-2"></i> Dirección
                                                    errada:
                                                    <strong>3</strong>
                                                </li>
                                                <li><i class="fas fa-box text-warning mr-2"></i> Falta de stock:
                                                    <strong>2</strong>
                                                </li>
                                                <li><i class="fas fa-truck-loading text-warning mr-2"></i> Falla logística:
                                                    <strong>2</strong>
                                                </li>
                                                <li><i class="fas fa-exclamation-circle text-warning mr-2"></i> Otros
                                                    motivos:
                                                    <strong>1</strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .total,
        .small-box .percentage {
            transition: opacity 0.5s ease;
        }

        .small-box .percentage {
            position: absolute;
            left: 0;
            opacity: 0;
        }

        .small-box:hover .total {
            opacity: 0;
        }

        .small-box:hover .percentage {
            opacity: 1;
        }

        .collapse tfoot,
        .collapsing tfoot,
        .collapse.show tfoot,
        .collapse tfoot th,
        .collapsing tfoot th,
        .collapse.show tfoot th {
            height: 1px !important;
            padding: 0px !important;
        }
    </style>
@stop

@section('js')

@stop
