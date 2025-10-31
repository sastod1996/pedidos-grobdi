@extends('adminlte::page')

@section('title', 'Especialidades')

@section('content')
@can('especialidad.index')
<div class="row justify-content-md-center">
    <div class="col-sm-8">
        <div class="grobdi-header">
            <div class="grobdi-title">
                <div>
                    <h2>🩺 Gestión de Especialidades</h2>
                    <p>Administra las especialidades médicas del sistema</p>
                </div>
                @can('especialidad.create')
                    <a class="btn" href="{{ route('especialidad.create') }}">
                        <i class="fa fa-plus"></i> Registrar Nueva
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
                    <table class="table table-bordered table-striped table-grobdi">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th width="220px">Opciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($especialidad as $especia)
                            <tr>
                                <td>{{ $especia->name }}</td>
                                <td>{{ $especia->description }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('especialidad.edit')
                                            <a class="btn btn-primary btn-sm" href="{{ route('especialidad.edit',$especia->id) }}">
                                                <i class="fas fa-pen"></i> Editar
                                            </a>
                                        @endcan
                                        @can('especialidad.destroy')
                                            <form action="{{ route('especialidad.destroy',$especia->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No hay información que mostrar</td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                {!! $especialidad->appends(request()->except('page'))->links() !!}

            </div>
        </div>

    </div>
</div>
@endcan
@stop

@section('css')
@stop

@section('js')
@stop
