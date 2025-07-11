@extends('adminlte::page')

@section('title', 'Muestras Registradas')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
    <div class="container">
        @include('messages')
        <h1 class="flex-grow-1 text-center">Muestras Registradas <hr></h1>

        <div class="header-tools">
            <a href="{{ route('muestras.create') }}" class="btn" style="background-color:rgb(255, 113, 130); color: white;">
                <i class="fas fa-plus"></i> Agregar Muestra
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="table_muestras">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre de la Muestra</th>
                        <th scope="col">Clasificación</th>
                        <th scope="col">Unidad de<br>Medida</th>
                        <th scope="col">Tipo de Muestra</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Observaciones</th>
                        <th scope="col">Doctor</th>
                        <th scope="col">Comentarios <br> de laboratorio</th>
                        <th scope="col">Creado por</th>
                        <th scope="col">Fecha/hora<br>Registrada</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($muestras as $index => $muestra)
                        <tr id="muestra_{{ $muestra->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="observaciones">{{ $muestra->nombre_muestra }}</td>
                            <td>{{ $muestra->clasificacion ? $muestra->clasificacion->nombre_clasificacion : 'Sin clasificación' }}</td>
                            <td>
                                @if($muestra->clasificacion && $muestra->clasificacion->unidadMedida)
                                    {{ $muestra->clasificacion->unidadMedida->nombre_unidad_de_medida }}
                                @else
                                    No asignada
                                @endif
                            </td>
                            <td>{{ ucfirst($muestra->tipo_muestra) ?? 'No asignado' }}</td>
                            <td>{{ $muestra->cantidad_de_muestra }}</td>
                            <td class="observaciones">{{ $muestra->observacion }}</td>
                            <td class="observaciones">{{ $muestra->name_doctor }}</td>
                            <td class="observaciones">{{ $muestra->comentarios }}</td>
                            <td>{{ $muestra->creator ? $muestra->creator->name : 'Desconocido' }}</td>
                            <td>
                                {{ $muestra->updated_at ? $muestra->updated_at->format('Y-m-d') : $muestra->created_at->format('Y-m-d') }}<br>
                                {{ $muestra->updated_at ? $muestra->updated_at->format('H:i:s') : $muestra->created_at->format('H:i:s') }}
                            </td>
                            <td>
                                <div class="w">
                                    @include('muestras.visitadoraMedica.show')
                                    <button class="btn btn-success mb-1 btn-sm" data-toggle="modal" data-target="#muestraModal{{ $muestra->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('muestras.edit', $muestra->id) }}" class="btn btn-primary mb-1 btn-sm">
                                        <i class="fas fa-edit"></i>   
                                    </a>
                                    <form action="{{ route('muestras.destroy', $muestra->id) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Desea eliminar esta muestra?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop 

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('css/muestras/labora.css') }}">
@stop

@section('js') 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
            <script>
            // Seleccionamos todos los botones con la clase 'verFotoBtn'
            document.querySelectorAll('.verFotoBtn').forEach(function(button) {
                button.addEventListener('click', function() {
                    // Encontramos el contenedor de foto correspondiente al botón
                    var fotoContainer = this.nextElementSibling;
                    var isVisible = fotoContainer.style.display === 'block';

                    // Alterna entre mostrar y ocultar la foto
                    fotoContainer.style.display = isVisible ? 'none' : 'block';

                    // Cambiar el texto del botón según el estado de la foto
                    this.innerHTML = isVisible ? '<i class="fas fa-eye"></i> Ver Foto' : '<i class="fas fa-eye-slash"></i> Ocultar Foto';
                });
            });
            $(document).ready(function() {
                $('#table_muestras').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
                    },
                    ordering: false,
                    responsive: true,
                    dom: '<"row"<"col-sm-12 col-md-12"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    pageLength: 10,
                    initComplete: function() {
                        $('.dataTables_filter')
                            .appendTo('.header-tools')
                            .addClass('text-right ml-auto')
                            .find('input')  
                            .attr('placeholder', 'Buscar por nombre de la muestra')
                            .end()  
                            .find('label')
                            .contents().filter(function() {
                                return this.nodeType === 3;
                            }).remove()
                            .end()
                            .prepend('Buscar:');
                    }
                });
            });
        </script>
@stop