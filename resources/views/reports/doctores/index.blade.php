@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    @can('reports.doctors')
    <div class="row">
        <div class="col-12">
            <x-grobdi.layout.tab-card
                id="reports-doctores-tabs"
                title="Fluctuación de Ventas"
                subtitle="Analiza a los doctores por perfil, tipo y seguimiento en una sola vista"
                :tabs="[
                    ['id' => 'doctor', 'label' => 'Doctor', 'icon' => 'fas fa-user-md'],
                    ['id' => 'tipo-doctor', 'label' => 'Tipo Doctor', 'icon' => 'fas fa-stethoscope'],
                    ['id' => 'seguimiento', 'label' => 'Seguimiento', 'icon' => 'fas fa-chart-line'],
                ]"
                active-tab="doctor"
            >
                <div class="tab-pane fade show active" id="doctor" role="tabpanel" aria-labelledby="reports-doctores-tabs-tab-doctor">
                    @include('reports.doctores.partials.doctor')
                </div>
                <div class="tab-pane fade" id="tipo-doctor" role="tabpanel" aria-labelledby="reports-doctores-tabs-tab-tipo-doctor">
                    @include('reports.doctores.partials.tipo-doctor')
                </div>
                <div class="tab-pane fade" id="seguimiento" role="tabpanel" aria-labelledby="reports-doctores-tabs-tab-seguimiento">
                    @include('reports.doctores.partials.seguimiento')
                </div>
            </x-grobdi.layout.tab-card>
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
