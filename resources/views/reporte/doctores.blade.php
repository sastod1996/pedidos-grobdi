@extends('adminlte::page')

@section('title', 'Reporte de Doctores')

@section('content_header')
<h1><i class="fas fa-user-md text-primary"></i> Reporte de Doctores</h1>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Estilos personalizados para reportes */
    .badge-soft-primary {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .bg-gradient-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
    }

    /* Scroll customizado */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Loading spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, .3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Chart containers */
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    .chart-responsive {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <div class="card bg-light">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="doctoresTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tipo-doctor-tab" data-toggle="tab" data-target="#tipo-doctor" type="button" role="tab">
                        <i class="fas fa-stethoscope"></i> Tipo Doctor
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="doctor-tab" data-toggle="tab" data-target="#doctor" type="button" role="tab">
                        <i class="fas fa-user-md"></i> Doctor
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="doctoresTabsContent">
                @include('reporte.componentes.doctores.tipo-doctor')
                @include('reports.doctores.partials.doctor')
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Chartjs', true)

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Datos desde el backend (mutable para actualizar con nuevos filtros)
    let datosDoctores = @json($data ?? []);
    console.log('Datos recibidos del backend:', datosDoctores);

    // Verificación de jQuery
    if (typeof $ === 'undefined') {
        console.error('jQuery no está cargado antes del script de doctores. Asegúrate de que AdminLTE o la plantilla incluya jQuery.');
    }

    $(document).ready(function() {
        console.log('Iniciando reportes de doctores...');

        // Configurar datepickers de rango (fecha inicio / fecha fin) para tipos de doctor
        flatpickr('#fecha_inicio_tipo_doctor', {
            dateFormat: 'Y-m-d',
            locale: 'es',
            allowInput: true
        });

        flatpickr('#fecha_fin_tipo_doctor', {
            dateFormat: 'Y-m-d',
            locale: 'es',
            allowInput: true
        });

        flatpickr('#anio_doctor', {
            dateFormat: 'Y',
            locale: 'es'
        });

        // Crear gráficos iniciales (solo los de tipos)
        crearGraficoTipoDoctor();
        crearGraficoPieTipoDoctor();
        // Eliminado doctor por defecto: se graficará solo cuando exista data concreta

        // Eventos con delegación (por si el contenido se re-renderiza)
        $(document).on('click', '#filtrar_tipo_doctor', function(e) {
            e.preventDefault();
            console.log('[DEBUG] Click en #filtrar_tipo_doctor');
            const fechaInicio = $('#fecha_inicio_tipo_doctor').val();
            const fechaFin = $('#fecha_fin_tipo_doctor').val();
            console.log('[DEBUG] Valores capturados:', {
                fechaInicio,
                fechaFin
            });
            cargarDatosTipoDoctor(fechaInicio, fechaFin);
        });

        $(document).on('click', '#limpiar_filtros_tipo_doctor', function(e) {
            e.preventDefault();
            console.log('[DEBUG] Click en #limpiar_filtros_tipo_doctor');
            $('#fecha_inicio_tipo_doctor').val('');
            $('#fecha_fin_tipo_doctor').val('');
            cargarDatosTipoDoctor('', '');
        });

        $('#filtrar_doctor').click(function() {
            const doctor = $('#buscador_doctor').val();
            const anio = $('#anio_doctor').val();

            if (doctor && anio) {
                // Mostrar info del doctor
                if (datosDoctores.doctores[doctor]) {
                    $('#doctor-nombre').text(doctor);
                    $('#doctor-info').show();
                    crearGraficoDoctor(doctor);
                    crearGraficoProductosDoctor(doctor);
                    alert('Mostrando datos de ' + doctor + ' para ' + anio);
                } else {
                    alert('Doctor no encontrado');
                }
            } else {
                alert('Ingresa nombre del doctor y año');
            }
        });

        $('#limpiar_doctor').click(function() {
            $('#buscador_doctor').val('');
            $('#anio_doctor').val('');
            $('#doctor-info').hide();
            alert('Filtros limpiados');
        });

        // Botones de descarga
        $('#descargar-excel-tipo-doctor').click(function() {
            alert('Descargando reporte de tipos de doctor en Excel...');
        });

        $('#descargar-excel-doctor').click(function() {
            alert('Descargando reporte del doctor en Excel...');
        });
    });

    function crearGraficoTipoDoctor() {
        const ctx = document.getElementById('tipoDoctorChart');
        if (!ctx) return;

        // Verificar si tenemos datos de ventas por mes
        if (!datosDoctores.ventasPorMes || !datosDoctores.ventasPorMes.datasets) {
            console.warn('No hay datos de ventas por mes disponibles');
            return;
        }

        window.tipoDoctorChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosDoctores.ventasPorMes.meses || ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                datasets: datosDoctores.ventasPorMes.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Tipo de Doctor (Mensual)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            },
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
        console.log('Gráfico de tipos de doctor creado');
    }

    function crearGraficoPieTipoDoctor() {
        const ctx = document.getElementById('tipoDoctorPieChart');
        if (!ctx) return;

        // Verificar si tenemos datos de tipos
        if (!datosDoctores.tipos || !datosDoctores.tipos.labels) {
            console.warn('No hay datos de tipos disponibles');
            return;
        }

        window.tipoDoctorPieChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: datosDoctores.tipos.labels,
                datasets: [{
                    data: datosDoctores.tipos.datos,
                    backgroundColor: ['#dc3545', '#28a745', '#ffc107'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Doctores por Tipo',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
        console.log('Gráfico de pie tipos de doctor creado');
    }

    function crearGraficoDoctor(doctorNombre) {
        const ctx = document.getElementById('doctorChart');
        if (!ctx) return;
        if (!datosDoctores.doctores || !datosDoctores.doctores[doctorNombre]) {
            console.warn('No hay datos para el doctor:', doctorNombre);
            return;
        }
        const doctor = datosDoctores.doctores[doctorNombre];
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        // Actualizar información del doctor
        $('#doctor-nombre').text(doctorNombre);
        $('#doctor-info p').text('Especialidad: ' + doctor.especialidad);

        // Destruir gráfico anterior si existe
        if (window.doctorChartInstance) {
            window.doctorChartInstance.destroy();
        }

        window.doctorChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: doctor.meses,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas Mensuales de ' + doctorNombre
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                }
            }
        });
        console.log('Gráfico del doctor creado para:', doctorNombre);
    }

    function crearGraficoProductosDoctor(doctorNombre) {
        const ctx = document.getElementById('doctorProductosChart');
        if (!ctx) return;
        if (!datosDoctores.doctores || !datosDoctores.doctores[doctorNombre]) {
            console.warn('No hay datos de productos para el doctor:', doctorNombre);
            return;
        }
        const doctor = datosDoctores.doctores[doctorNombre];

        // Destruir gráfico anterior si existe
        if (window.doctorProductosChartInstance) {
            window.doctorProductosChartInstance.destroy();
        }

        window.doctorProductosChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: doctor.productos,
                datasets: [{
                    label: 'Ventas por Producto (S/)',
                    data: doctor.datosProductos,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107'],
                    borderColor: ['#007bff', '#28a745', '#ffc107'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Productos Más Vendidos por ' + doctorNombre
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                }
            }
        });
        console.log('Gráfico de productos del doctor creado para:', doctorNombre);
    }

    // Función para cargar datos de tipo de doctor via AJAX
    function cargarDatosTipoDoctor(fechaInicio = '', fechaFin = '') {
        console.log('[DEBUG] Inicia cargarDatosTipoDoctor()');
        console.log('[DEBUG] Parámetros recibidos -> Fecha Inicio:', fechaInicio || 'Sin filtro', 'Fecha Fin:', fechaFin || 'Sin filtro');

        // Mostrar loading en tabla
        const loadingHtml = `
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="loading-spinner"></div>
                <span class="ml-2">Cargando datos...</span>
            </td>
        </tr>
    `;
        $('#tablaTipoDoctorBody').html(loadingHtml);

        // Deshabilitar botón mientras carga
        $('#filtrar_tipo_doctor').prop('disabled', true).html('<i class="loading-spinner"></i> Cargando...');

        // Construir datos de la petición
        const requestData = {};

        // Enviar fechas solo si tienen valor
        if (fechaInicio && fechaInicio.trim() !== '') {
            requestData.fecha_inicio_tipo_doctor = fechaInicio;
        }
        if (fechaFin && fechaFin.trim() !== '') {
            requestData.fecha_fin_tipo_doctor = fechaFin;
        }

        console.log('[DEBUG] Payload a enviar:', requestData);
        $.ajax({
            url: '/api/reportes/doctores',
            method: 'GET',
            data: requestData,
            success: function(response) {
                console.log('[DEBUG] Success AJAX /api/reportes/doctores status 200');
                console.log('[DEBUG] Response completa:', response);

                // Actualizar datos globales
                window.datosDoctores = response;
                datosDoctores = response;

                // Actualizar tabla
                actualizarTablaTipoDoctor(response.estadisticasTabla);

                // Recrear gráficos
                recrearGraficos();

                // Actualizar timestamp
                $('#ultima-actualizacion').text(new Date().toLocaleString('es-ES'));

                // Mostrar información de filtros aplicados
                mostrarFiltrosAplicados(requestData);
            },
            error: function(xhr, status, error) {
                console.error('[DEBUG] Error AJAX /api/reportes/doctores');
                console.error('[DEBUG] status:', status, 'httpStatus:', xhr.status, 'error:', error);
                console.error('[DEBUG] responseText:', xhr.responseText);
                $('#tablaTipoDoctorBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        <br>Error al cargar datos
                        <br><small>Por favor, intente nuevamente</small>
                    </td>
                </tr>
            `);
            },
            complete: function() {
                // Rehabilitar botón
                $('#filtrar_tipo_doctor').prop('disabled', false).html('<i class="fas fa-filter"></i> Filtrar');
                console.log('[DEBUG] AJAX completado');
            }
        });
    }

    // Función para actualizar la tabla
    function actualizarTablaTipoDoctor(estadisticas) {
        let html = '';
        let total = null;

        if (estadisticas && estadisticas.length > 0) {
            estadisticas.forEach(function(item) {
                if (item.tipo === 'Total') {
                    total = item;
                } else {
                    html += `
                    <tr class="animate__animated animate__fadeIn">
                        <td>
                            <span class="badge badge-soft-primary">${item.tipo}</span>
                        </td>
                        <td class="text-center">
                            <strong>${item.total_doctores}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">${item.porcentaje}%</span>
                        </td>
                        <td class="text-end">
                            <span class="text-success fw-bold">S/ ${item.promedio_ventas.toLocaleString()}</span>
                        </td>
                    </tr>
                `;
                }
            });
        } else {
            html = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle"></i> No hay datos disponibles
                </td>
            </tr>
        `;
        }

        $('#tablaTipoDoctorBody').html(html);

        // Actualizar el pie de la tabla (footer fijo)
        if (total) {
            const footerHtml = `
            <tr class="table-secondary">
                <th style="min-width: 120px;">
                    <i class="fas fa-calculator"></i> ${total.tipo}
                </th>
                <th style="min-width: 80px;" class="text-center">
                    <span class="badge bg-primary">${total.total_doctores}</span>
                </th>
                <th style="min-width: 60px;" class="text-center">
                    <span class="badge bg-dark">${total.porcentaje}%</span>
                </th>
                <th style="min-width: 120px;" class="text-end">
                    <span class="text-primary fw-bold">S/ ${total.promedio_ventas.toLocaleString()}</span>
                </th>
            </tr>
        `;
            $('.card-body .bg-light table tfoot').html(footerHtml);
        }
    }

    // Función para recrear gráficos
    function recrearGraficos() {
        // Destruir gráficos existentes
        if (window.tipoDoctorChartInstance) {
            window.tipoDoctorChartInstance.destroy();
        }
        if (window.tipoDoctorPieChartInstance) {
            window.tipoDoctorPieChartInstance.destroy();
        }

        // Crear nuevos gráficos
        crearGraficoTipoDoctor();
        crearGraficoPieTipoDoctor();
    }

    // Función para mostrar filtros aplicados
    function mostrarFiltrosAplicados(filtros) {
        let textoFiltros = '';
        let partes = [];

        if (filtros.fecha_inicio_tipo_doctor) {
            partes.push(`Desde ${filtros.fecha_inicio_tipo_doctor}`);
        }
        if (filtros.fecha_fin_tipo_doctor) {
            partes.push(`Hasta ${filtros.fecha_fin_tipo_doctor}`);
        }

        // Compatibilidad retro (si llegan filtros antiguos)
        if (filtros.anio_tipo_doctor && !filtros.fecha_inicio_tipo_doctor && !filtros.fecha_fin_tipo_doctor) {
            partes.push(`Año ${filtros.anio_tipo_doctor}`);
        }
        if (filtros.mes && !filtros.fecha_inicio_tipo_doctor && !filtros.fecha_fin_tipo_doctor) {
            const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];
            partes.push(`${meses[filtros.mes]}`);
        }

        if (partes.length > 0) {
            textoFiltros = `Filtrado: ${partes.join(' - ')}`;
        } else {
            textoFiltros = 'Todos los datos';
        }

        const smallElement = $('#subtitulo-grafico-tipo');
        if (smallElement.length) {
            smallElement.html(`<i class="fas fa-info-circle"></i> ${textoFiltros}`);
        }
    }
</script>
@endsection