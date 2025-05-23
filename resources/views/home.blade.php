@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Indicadores</h1>
@stop

@section('content')
    <p>Bienvenidos</p>
    <!-- <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header">
                    <label>Indicadores de Pedidos</label>
                </div>
                <div class="card-body">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>
    </div> -->
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // const ctx = document.getElementById('myChart');

        // new Chart(ctx, {
        //     type: 'bar',
        //     data: {
        //         labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        //         datasets: [{
        //             label: '# of Votes',
        //             data: [12, 19, 3, 5, 2, 3],
        //             borderWidth: 1
        //         }]
        //     },
        //     options: {
        //         scales: {
        //             y: {
        //                 beginAtZero: true
        //             }
        //         }
        //     }
        // });
    </script>
    
@stop