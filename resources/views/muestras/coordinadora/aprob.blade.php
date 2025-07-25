@extends('adminlte::page')

@section('title', 'Aprobacion Coordinadora')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')    
    <div class="container">
    @include('messages')
        <h1 class="flex-grow-1 text-center">Estado de las Muestras<hr></h1>
        <a title="Ver detalles" href="{{ route('muestras.createCO') }}" class="btn btn-success mb-2">
                    <i class="fas fa-plus-circle mr-1"></i> Agregar Muestra
            </a>
        <div class="header-tools d-flex justify-content-end align-items-center mb-2" style="gap: 10px;">
            <div id="datatable-search-wrapper" class="flex-grow-1"></div>
            <form id="exportExcelForm" method="POST" action="{{ route('muestras.exportarExcelCO') }}">
                @csrf
                <input type="hidden" name="ids" id="excelExportIds">
                <button type="submit" class="btn btn-outline-success" style="white-space:nowrap;">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </form>
        </div> 
        <div class="table-responsive">
            <table class="table table-hover" id="table_muestras">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre de la Muestra</th>
                        <th scope="col">Clasificación</th>
                        <th scope="col">Tipo de Muestra</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col" class="th-small">Aprobado<br> J. Comercial</th>
                        <th scope="col" class="th-small">Aprobado<br> Coordinadora</th>
                        <th>Creado por</th>
                        <th>Doctor</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Fecha/hora Entrega</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($muestras as $index => $muestra)
                        <tr id="muestra_{{ $muestra->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="observaciones">{{ $muestra->nombre_muestra }}</td>
                            <td>{{ $muestra->clasificacion ? $muestra->clasificacion->nombre_clasificacion : 'Sin clasificación' }}</td>
                            <td>{{ $muestra->tipo_muestra ?? 'No asignado' }}</td>
                            <td>{{ $muestra->cantidad_de_muestra }}</td>
                            <td>
                                <input type="checkbox" class="aprobacion-jefe" disabled {{ $muestra->aprobado_jefe_comercial ? 'checked' : '' }}>
                            </td>
                            <td>
                                <input type="checkbox" class="aprobado-coordinadora" data-id="{{ $muestra->id }}" {{ $muestra->aprobado_coordinadora ? 'checked' : '' }} {{ !$muestra->aprobado_jefe_comercial ? 'disabled' : '' }}>
                            </td>
                            <td>{{ $muestra->creator ? $muestra->creator->name : 'Desconocido' }}</td>
                            <td class="observaciones">{{ $muestra->name_doctor }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $muestra->estado == 'Pendiente' ? 'red' : 'green' }}; color: white; padding: 5px;">
                                    {{ $muestra->estado }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('muestras.actualizarFechaEntrega', $muestra->id) }}" method="POST" id="fecha_form_{{ $muestra->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="datetime-local" name="fecha_hora_entrega" class="form-control"
                                        value="{{ old('fecha_hora_entrega', $muestra->fecha_hora_entrega ? \Carbon\Carbon::parse($muestra->fecha_hora_entrega)->format('Y-m-d\TH:i') : '') }}"
                                        onchange="document.getElementById('fecha_form_{{ $muestra->id }}').submit();"
                                        id="fecha_{{ $muestra->id }}"
                                        min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                                </form>
                            </td>
                            <td>
                            <div class="w">
                                @include('muestras.coordinadora.showCo')
                                <button class="btn btn-success btn-sm mb-1"  data-toggle="modal" data-target="#muestraModal{{ $muestra->id }}">
                                    <i class="fas fa-binoculars"></i>
                                </button>
                                <a href="{{ route('muestras.editCO', $muestra->id) }}" class="btn btn-primary btn-sm mb-1">
                                    <i class="fas fa-edit"></i>   
                                </a>
                                    <form action="{{ route('muestras.destroyCO', $muestra->id) }}" method="post">
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
    <link rel="stylesheet" href="{{ asset('css/muestras/labora.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
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
            // Función para manejar la aprobación de coordinadora
            function actualizarAprobacion(id, field, value) {
                $.ajax({
                    url: `{{ url('muestras') }}/${id}/actualizar-aprobacion`,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: '{{ csrf_token() }}',
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        console.log(response.message);
                    },
                    error: function(xhr) {
                        alert('Error al actualizar la aprobación.');
                    }
                });
            }

            // Función para manejar el hover y tooltips
            function setupCheckboxHover() {
                $('.aprobado-coordinadora').closest('td').hover(function() {
                    var checkbox = $(this).find('.aprobado-coordinadora');
                    if (checkbox.prop('disabled')) {
                        $(this).attr('title', '⚠ Solo se puede marcar si el Jefe Comercial ha aprobado');
                    } else {
                        $(this).removeAttr('title');
                    }
                });

                $('.aprobacion-jefe').closest('td').hover(function() {
                    var checkbox = $(this).find('.aprobacion-jefe');
                    if (checkbox.prop('disabled')) {
                        $(this).attr('title', '⚠ Solo el Jefe Comercial puede marcar');
                    } else {
                        $(this).removeAttr('title');
                    }
                });
            }

            // Función para manejar el cambio en checkboxes de jefe comercial
            function setupJefeCheckboxChange() {
                $('.aprobacion-jefe').off('change').on('change', function() {
                    var isChecked = $(this).is(':checked');
                    var id = $(this).closest('tr').find('.aprobado-coordinadora').data('id');
                    $('.aprobado-coordinadora[data-id="' + id + '"]').prop('disabled', !isChecked);
                });
            }

            // Función para manejar el click/touch en checkboxes
            function setupCheckboxClickHandlers() {
                $('.aprobado-coordinadora, .aprobacion-jefe').off('click touchstart').on('click touchstart', function(e) {
                    if ($(this).prop('disabled')) {
                        e.preventDefault();
                        alert('⚠ ' + ($(this).hasClass('aprobado-coordinadora') 
                            ? 'Solo se puede marcar si el Jefe Comercial ha aprobado' 
                            : 'Solo el Jefe Comercial puede marcar'));
                    }
                });
            }

            function setupCoordinadoraCheckboxChange() {
                $('.aprobado-coordinadora').off('change').on('change', function() {
                    // Si ya está marcado, no permitir desmarcarlo
                    if ($(this).is(':checked')) {
                        var id = $(this).data('id');
                        var value = 1;
                        actualizarAprobacion(id, 'aprobado_coordinadora', value);
                    } else {
                        // Si intenta desmarcar, restaurarlo a marcado
                        $(this).prop('checked', true);
                    }
                });
}

            // Configuración de Pusher
            Pusher.logToConsole = true;
            var pusher = new Pusher('e4c5eef429639dfca470', { cluster: 'us2' });
            var channel = pusher.subscribe('muestras');

            // Configuración de notificaciones
            var MAX_NOTIFICATIONS = 4;
            var STORAGE_KEY = 'persistentNotificationsQueue';

            // Función para actualizar la tabla via AJAX
            function refreshTable() {
                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    success: function(data) {
                        var newTable = $(data).find('#table_muestras').html();
                        $('#table_muestras').html(newTable);
                        
                        // Adjuntar manejadores de eventos después de refrescar
                        attachEventHandlers();
                    }
                });
            }

            // Función para adjuntar los manejadores de eventos
            function attachEventHandlers() {
                setupCheckboxClickHandlers();
                setupJefeCheckboxChange();
                setupCoordinadoraCheckboxChange();
                setupCheckboxHover();
            }
            // Función para manejar la cola de notificaciones
            function manageNotificationQueue(type, title, message) {
                // Obtener cola actual de localStorage
                var notificationsQueue = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                
                // Crear ID único para la notificación
                var notificationId = type + '-' + title + '-' + message;
                
                // Verificar si ya existe en la cola
                var exists = notificationsQueue.some(n => n.id === notificationId);
                if (exists) return;
                
                // Agregar nueva notificación
                notificationsQueue.push({
                    id: notificationId,
                    type: type,
                    title: title,
                    message: message,
                    timestamp: new Date().getTime()
                });
                
                // Limpiar notificaciones antiguas si excedemos el máximo
                if (notificationsQueue.length > MAX_NOTIFICATIONS) {
                    // Eliminar la más antigua (FIFO)
                    notificationsQueue.shift();
                }
                
                // Guardar en localStorage
                localStorage.setItem(STORAGE_KEY, JSON.stringify(notificationsQueue));
                
                // Mostrar todas las notificaciones en cola
                displayNotificationQueue();
            }

            // Función para mostrar la cola de notificaciones
            function displayNotificationQueue() {
                // Limpiar notificaciones actuales
                toastr.clear();
                
                // Obtener cola de notificaciones
                var notificationsQueue = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                
                // Mostrar cada notificación
                notificationsQueue.forEach(notification => {
                    toastr[notification.type](notification.message, notification.title, {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        positionClass: 'toast-top-right',
                        enableHtml: true,
                        onHidden: function() {
                            // Al cerrar una notificación, eliminarla de la cola
                            removeNotificationFromQueue(notification.id);
                        }
                    });
                });
            }


            // Función para eliminar una notificación de la cola
            function removeNotificationFromQueue(notificationId) {
                var notificationsQueue = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                notificationsQueue = notificationsQueue.filter(n => n.id !== notificationId);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(notificationsQueue));
            }

            // Cargar notificaciones al iniciar
            function loadPersistentNotifications() {
                displayNotificationQueue();
            }

            // Eventos de Pusher
            channel.bind('muestra.creada', function(data) {
                console.log('Nueva muestra creada:', data);
                var muestra = data.muestra;
                
                refreshTable();
                
                setTimeout(function() {
                    var lastRow = $('#table_muestras tbody tr').last();
                   
                    
                    manageNotificationQueue(
                        'success', 
                        '<strong>Nueva Muestra Creada</strong>', 
                        `Nombre: <strong>${muestra.nombre_muestra}</strong><br><small><strong>Fecha de creación:</strong> ${muestra.fecha_creacion}</small>`
                    );
                }, 500);
            });

            channel.bind('muestra.actualizada', function(data) {
                console.log('Muestra actualizada:', data);
                var muestra = data.muestra;
                
                refreshTable();
                
                setTimeout(function() {
                    var row = $('#muestra_' + muestra.id);
                    if (row.length > 0) {
                        
                        var fechaActualizacion = new Date(muestra.fecha_actualizacion).toLocaleString();
                        
                        manageNotificationQueue(
                            'info', 
                            'Muestra Actualizada', 
                            `Nombre: <strong>${muestra.nombre_muestra}</strong><br><small><strong>Fecha de creación: </strong>${fechaActualizacion}</small>`
                        );
                    }
                }, 500);
            });

            $(document).ready(function() {
                // Limpiar notificaciones existentes al cargar
                toastr.clear();
                
                // Cargar notificaciones persistentes
                loadPersistentNotifications();
                
                // Adjuntar manejadores de eventos
                attachEventHandlers();
                
            });
  </script>
  <script>
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
                 function stripHtml(html) {
                        return String(html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    }
                    $('#exportExcelForm').on('submit', function(e) {
                        var data = table.rows({ search: 'applied' }).nodes();
                        var ids = [];
                        data.each(function(row) {
                            var id = $(row).attr('id');
                            if (id && id.startsWith('muestra_')) {
                                ids.push(id.replace('muestra_', ''));
                            }
                        });
                        $('#excelExportIds').val(JSON.stringify(ids));
                    });
            });
  </script>
@stop
