@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-pump-medical mr-1 text-danger"></i>Reporte de Muestras</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Reporte de Muestras</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Detalle adicional -->
    <div class="card card-danger card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="ventasTabs" role="tablist">
                @foreach ($arrayTabs as $index => $tab)
                    @php
                        $isFirst = $index === 0;
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $isFirst ? 'active' : '' }}" id="{{ $tab['name'] }}-tab"
                            data-target="#{{ $tab['name'] }}" role="tab" data-toggle="pill" href="#{{ $tab['name'] }}"
                            aria-controls="{{ $tab['name'] }}" aria-selected="{{ $isFirst }}">
                            <i class="{{ $tab['icon'] }}"></i> {{ ucfirst($tab['name']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="muestrasTabsContent">
                @foreach ($arrayTabs as $index => $tab)
                    @php
                        $isFirst = $index === 0;
                    @endphp
                    <div class="tab-pane fade {{ $isFirst ? 'active show' : '' }}" id="{{ $tab['name'] }}"
                        role="tabpanel">
                        @include('reports.muestras.partials.' . $tab['name'])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-foot-fixed {
            position: sticky;
            bottom: 0;
            z-index: 2;
        }

        .table-responsive::-webkit-scrollbar {
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #d40c0c63;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #D40C0D;
        }
    </style>
@stop

@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Flatpickr', true)

@section('js')

    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script src="{{ asset('js/table-helpers.js') }}"></script>
    <script src="{{ asset('js/get-money-format.js') }}"></script>
    <script src="{{ asset('js/generate-hsl-color.js') }}"></script>
    <script src="{{ asset('js/autocomplete-input.js') }}"></script>

    @stack('partial-js')

@stop
