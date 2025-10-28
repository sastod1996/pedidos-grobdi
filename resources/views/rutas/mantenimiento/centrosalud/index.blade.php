@extends('adminlte::page')

@section('title', 'Centro de Salud')

@section('content')
@can('centrosalud.index')
<div class="row justify-content-md-center">
    <div class="col-sm-10">
        <div class="grobdi-header">
            <div class="grobdi-title">
                <div>
                    <h2>🏥 Gestión de Centros de Salud</h2>
                    <p>Administra los centros de salud del sistema</p>
                </div>
                @can('centrosalud.create')
                    <a class="btn" href="{{ route('centrosalud.create') }}">
                        <i class="fa fa-plus"></i> Registrar Nuevo
                    </a>
                @endcan
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                @session('success')
                    <div class="alert alert-success" role="alert"> {{ $value }} </div>
                @endsession

                <div class="table table-responsive">
                    <table id="miTabla" class="table table-bordered table-striped table-grobdi">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Dirección</th>
                                <th>Posición</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($centrosalud as $centrosa)
                            <tr class={{ $centrosa->state == 0 ? 'table-danger': ''}}>
                                <td>{{ $centrosa->name }}</td>
                                <td>{{ $centrosa->description }}</td>
                                <td>{{ $centrosa->adress }}</td>
                                <td>{{ $centrosa->latitude }} - {{ $centrosa->longitude }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('centrosalud.edit')
                                            <a class="btn btn-primary btn-sm" href="{{ route('centrosalud.edit',$centrosa->id) }}">
                                                <i class="fas fa-pen"></i> Editar
                                            </a>
                                        @endcan
                                        @can('centrosalud.destroy')
                                            <form action="{{ route('centrosalud.destroy',$centrosa->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                @if($centrosa->state == 1)
                                                    <button type="submit" class="btn btn-danger btn-sm">Inhabilitar</button>
                                                @else
                                                    <button type="submit" class="btn btn-success btn-sm">Habilitar</button>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No hay información que mostrar</td>
                            </tr>
                        @endforelse
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
    $(document).ready(function() {
        $('#miTabla').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json'
            },
            pageLength: 25,
            lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ]
        });
    });
</script>
@stop
