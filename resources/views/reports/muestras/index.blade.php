@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    @php
        $muestrasTabs = collect($arrayTabs)->map(function ($tab) {
            return [
                'id' => $tab['name'],
                'label' => ucfirst($tab['name']),
                'icon' => $tab['icon'] ?? null,
            ];
        })->toArray();
        $muestrasActiveTab = $muestrasTabs[0]['id'] ?? null;
    @endphp

    <x-grobdi.layout.tab-card
        id="reports-muestras-tabs"
        title="Reporte de Muestras"
        subtitle="Explora métricas generales, por doctor y otros segmentos en una sola vista"
        :tabs="$muestrasTabs"
        :active-tab="$muestrasActiveTab"
    >
        @foreach ($arrayTabs as $index => $tab)
            @php
                $isFirst = $index === 0;
            @endphp
            <div class="tab-pane fade {{ $isFirst ? 'active show' : '' }}" id="{{ $tab['name'] }}" role="tabpanel"
                aria-labelledby="reports-muestras-tabs-tab-{{ $tab['name'] }}">
                @include('reports.muestras.partials.' . $tab['name'])
            </div>
        @endforeach
    </x-grobdi.layout.tab-card>
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
