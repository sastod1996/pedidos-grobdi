@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-motorcycle mr-1 text-danger"></i> Reporte de Muestras</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Reporte Muestras</li>
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
                        <div class="col-6 position-relative">
                            <canvas id="resume-tipo-muestras-chart" height="300px">
                            </canvas>
                            @include('empty-chart', [
                                'dataLength' => $data['general_stats']['total_muestras'],
                            ])
                        </div>
                        <div class="col-6 position-relative">
                            <canvas id="resume-tipo-frasco-chart" height="300px">
                            </canvas>
                            @include('empty-chart', [
                                'dataLength' => $data['general_stats']['total_muestras'],
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('plugins.Chartjs', true)

@section('js')

    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/generate-hsl-color.js') }}"></script>

    <script>
        const data = @json($data);
        console.log(data);
        const resumeTiposMuestraLabels = data.data.by_tipo_muestra.map(i => i.tipo);
        const resumeTiposFrascoLabels = data.data.by_tipo_frasco.map(i => i.tipo_frasco);
        const datasets = [{
            label: 'Cantidad de muestras',
            data: [12, 13, 15],
            borderColor: '#fff',
            backgroundColor: generateHslColors(resumeTiposMuestraLabels)
        }]
        const barChartDataset = [{
            label: 'Cantidad de muestras',
            data: [20, 10],
            borderColor: generateHslColors(resumeTiposFrascoLabels),
            borderWidth: 1.5,
            backgroundColor: generateHslColors(resumeTiposFrascoLabels, 0.5)
        }]

        const resumeTipoMuestrasChart = createChart('#resume-tipo-muestras-chart', resumeTiposMuestraLabels,
            datasets, 'pie');

        const resumeTipoFrascoChart = createChart('#resume-tipo-frasco-chart', resumeTiposFrascoLabels,
            barChartDataset, 'bar');
    </script>

@stop
