@extends('adminlte::page')

@section('title', 'Rutas Visitadora')

@section('content')
    @can('rutasvisitadora.ListarMisRutas')
        <div class="grobdi-header">
            <div class="grobdi-title">
                <div>
                    <h2>📍 Rutas de las visitadoras</h2>
                    <p>Rutas asignadas a las visitadoras</p>
                </div>
            </div>
        </div>
        @php
            \Carbon\Carbon::setLocale('es');
            $mes = \Carbon\Carbon::now()->translatedFormat('F');
        @endphp

        <p>Mes actual: {{ ucfirst($mes) }}</p>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <label>Lista de semanas</label>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive">
                            <table class="table table-grobdi">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Ver Doctores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listas as $lista)
                                    <tr>
                                        <td>{{ $lista->lista->name }}</td>
                                        <td>{{ $lista->fecha_inicio }}</td>
                                        <td>{{ $lista->fecha_fin }}</td>
                                        <td>
                                            @can('rutasvisitadora.listadoctores')
                                                <a class="btn btn-primary btn-sm" href="{{ route('rutasvisitadora.listadoctores',$lista->id) }}"><i class=" fa fa-eye"></i>Doctores</a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@stop

@section('css')

@stop

@section('js')
    <script>
    </script>

@stop
