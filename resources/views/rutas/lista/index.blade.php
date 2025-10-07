@extends('adminlte::page')

@section('title', 'Listas')

@section('content_header')
    <h1>Listas</h1>
@stop

@section('content')
<div class="card mt-2">
    <div class="card-header">
        <div class="d-grid gap-2 d-md-flex justify-content-md-medium">
            <a class="btn btn-success btn-sm" href="{{ route('lista.create') }}"> <i class="fa fa-plus"></i> Registrar datos</a>
        </div>
    </div>
    <div class="card-body">
    @session('success')
        <div class="alert alert-success" role="alert"> {{ $value }} </div>
    @endsession
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Recuperacion</th>
                    <th>Zona</th>
                    <th>Distritos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
  
            <tbody>
            @forelse ($listas as $lista)
                <tr>
                    <td>{{ $lista->name }}</td>
                    <td>{{ $lista->recovery ? 'Si' : 'No' }}</td>
                    <td>{{ $lista->zone->name?? '' }}</td>
                    <td>
                        @foreach ($lista->distritos as $distrito)
                        {{$distrito->name }}<br>
                        @endforeach
                    </td>
                        <td>
                        <a class="btn btn-primary btn-sm" href="{{ route('lista.edit',$lista->id) }}"><i class="fa-solid fa-pen-to-square"></i> Actualizar</a>
                        <!-- <form action="{{ route('lista.destroy',$lista->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            @if($lista->state == 1)
                                <button type="submit" class="btn btn-danger btn-sm">Inhabilitar</button>
                            @else
                                <button type="submit" class="btn btn-success btn-sm">Habilitar</button>
                            @endif
                        </form> -->
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay información que mostrar</td>
                </tr>
            @endforelse
            </tbody>
  
        </table>
    </div>
</div>

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stop