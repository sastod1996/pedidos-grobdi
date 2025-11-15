@extends('adminlte::page')

@section('title', 'Reporte de Ventas')

@section('content')
    @can('reports.ventas')
    @php
        $ventasTabs = [
            ['id' => 'general', 'label' => 'General', 'icon' => 'fas fa-chart-bar'],
            ['id' => 'visitadoras', 'label' => 'Visitadoras', 'icon' => 'fas fa-user-tie'],
            ['id' => 'productos', 'label' => 'Productos', 'icon' => 'fas fa-box'],
            ['id' => 'provincias', 'label' => 'Provincias', 'icon' => 'fas fa-globe-americas'],
        ];
    @endphp

    <x-grobdi.layout.tab-card
        id="reports-ventas-tabs"
        title="Reporte Comercial - Ventas"
        subtitle="Navega entre KPIs generales, visitadoras, productos y provincias"
        :tabs="$ventasTabs"
        active-tab="general"
    >
        <div class="tab-pane fade show active" id="general" role="tabpanel"
            aria-labelledby="reports-ventas-tabs-tab-general">
            @include('reports.ventas.partials.general')
        </div>
        <div class="tab-pane fade" id="visitadoras" role="tabpanel"
            aria-labelledby="reports-ventas-tabs-tab-visitadoras">
            @include('reports.ventas.partials.visitadoras')
        </div>
        <div class="tab-pane fade" id="productos" role="tabpanel"
            aria-labelledby="reports-ventas-tabs-tab-productos">
            @include('reports.ventas.partials.productos')
        </div>
        <div class="tab-pane fade" id="provincias" role="tabpanel"
            aria-labelledby="reports-ventas-tabs-tab-provincias">
            @include('reports.ventas.partials.provincias')
        </div>
    </x-grobdi.layout.tab-card>
    @endcan
@stop

@section('plugins.Moment', true)
@section('plugins.DateRangePicker', true)
@section('plugins.Chartjs', true)
@section('plugins.Toastr', true)
@section('plugins.Flatpickr', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/table-helpers.js') }}"></script>
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script src="{{ asset('js/get-money-format.js') }}"></script>
    <script src="{{ asset('js/generate-hsl-color.js') }}"></script>

    @stack('partial-js')

@endsection
