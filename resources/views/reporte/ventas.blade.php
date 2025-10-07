@extends('adminlte::page')

@section('title', 'Reporte de Ventas')

@section('content_header')
<h1><i class="fas fa-chart-line text-primary"></i> Reporte de Ventas</h1>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="ventasTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="visitadora-tab" data-toggle="tab" data-target="#visitadora"
                        type="button" role="tab">
                        <i class="fas fa-user-md"></i> Visitadora
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="producto-tab" data-toggle="tab" data-target="#producto" type="button"
                        role="tab">
                        <i class="fas fa-box"></i> Producto
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="provincia-tab" data-toggle="tab" data-target="#provincia"
                        type="button" role="tab">
                        <i class="fas fa-map-marker-alt"></i> Provincia
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="general-tab" data-toggle="tab" data-target="#general" type="button"
                        role="tab">
                        <i class="fas fa-chart-bar"></i> General
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="ventasTabsContent">
                @include('reporte.componentes.ventas.visitadora')
                @include('reporte.componentes.ventas.producto')
                @include('reporte.componentes.ventas.provincia')
                @include('reporte.componentes.ventas.general', ['data' => $data])
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Moment', true)
@section('plugins.DateRangePicker', true)
@section('plugins.Chartjs', true)

@section('js')
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('js')

<script>
    // URL del endpoint construida con helper de Laravel para evitar problemas de rutas/base path
    const API_VENTAS_PROVINCIAS = "{{ route('api.reportes.ventas-provincias') }}";
    const API_PEDIDOS_DEPARTAMENTO = "{{ route('api.reportes.pedidos-departamento') }}";
    // Toast helper (SweetAlert2)
    function toast(message, icon = 'info') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
        } else {
            console.log('[toast]', icon, message);
        }
    }
    // Datos en memoria
    const datosVentas = {
        visitadoras: {
            labels: ['Visitadora Sur', 'Visitadora Norte', 'Visitadora Centro'],
            datos: [1500, 2000, 1800],
            visitas: [45, 60, 55],
            colores: ['#dc3545', '#007bff', '#ffc107']
        },
        productos: {
            labels: ['Vitaminas Prenatales', 'Suplementos de Hierro', 'Ácido Fólico', 'Calcio'],
            datos: [2400, 1425, 850, 975],
            cantidades: [120, 95, 85, 65],
            colores: ['#28a745', '#17a2b8', '#ffc107', '#6f42c1']
        },
        provincias: {
            labels: ['Ica', 'Arequipa', 'Lima'],
            datos: [2500, 2200, 800],
            visitas: [80, 75, 30],
            colores: ['#dc3545', '#28a745', '#007bff']
        },
        reportesMes: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
                'Octubre', 'Noviembre', 'Diciembre'
            ],
            datos: [4200, 3800, 5300, 4900, 5600, 5850, 5200, 6100, 5800, 6300, 6000, 6500],
            colores: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#fd7e14', '#e83e8c',
                '#20c997', '#6c757d', '#343a40', '#f8f9fa'
            ]
        },
        ventasDia: {
            labels: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16',
                '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'
            ],
            datos: [150, 200, 180, 220, 190, 210, 230, 250, 240, 260, 280, 270, 290, 300, 310, 320, 330, 340, 350,
                360, 370, 380, 390, 400, 410, 420, 430, 440, 450, 460
            ]
        },
        tendencia: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datos: [4200, 3800, 5300, 4900, 5600, 5850]
        }
    };

    $(document).ready(function() {

        // Configurar datepickers
        flatpickr('#fecha_inicio', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        flatpickr('#fecha_fin', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        flatpickr('#fecha_inicio_provincia', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        flatpickr('#fecha_fin_provincia', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        flatpickr('#mes_general', {
            dateFormat: 'Y-m',
            locale: 'es'
        });

        flatpickr('#anio_general', {
            dateFormat: 'Y',
            locale: 'es'
        });

        flatpickr('#fecha_inicio_producto', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        flatpickr('#fecha_fin_producto', {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });

        // Crear gráficos (protegido con try/catch para no bloquear el binding de eventos si algo falla)
        try {
            crearGraficoVisitadoras();
            crearGraficoPieVisitadoras();
            crearGraficoProductos();
            cargarProvinciasDesdeAPI();
            crearGraficoReportesMes();
            crearGraficoVentasDia();
            crearGraficoTendencia();
        } catch (err) {
            console.error('[ventas.init] Error inicializando gráficos/reportes:', err);
        }

        // Eventos
        $('#filtrar').click(function() {
            const inicio = $('#fecha_inicio').val();
            const fin = $('#fecha_fin').val();

            if (inicio && fin) {
                toast('Aplicando filtros: ' + inicio + ' → ' + fin, 'success');
                // Aquí actualizarías los gráficos con datos filtrados
            } else {
                toast('Selecciona ambas fechas', 'warning');
            }
        });

        $('#limpiar').click(function() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            toast('Filtros limpiados', 'info');
        });

        $(document).on('click', '#filtrar_provincia', function() {
            console.log('[provincia] Click en Aplicar Filtros');
            const inicio = $('#fecha_inicio_provincia').val();
            const fin = $('#fecha_fin_provincia').val();

            // Validar fechas si ambas están seleccionadas
            if (inicio && fin && new Date(inicio) > new Date(fin)) {
                toast('La fecha de inicio no puede ser mayor que la fecha de fin', 'warning');
                return;
            }

            cargarProvinciasDesdeAPI();
        });

        // Enter en los inputs de fecha también dispara filtrar
        $(document).on('keydown', '#fecha_inicio_provincia, #fecha_fin_provincia', function(e) {
            if (e.key === 'Enter') {
                $('#filtrar_provincia').click();
            }
        });

        $(document).on('click', '#limpiar_provincia', function() {
            $('#fecha_inicio_provincia').val('');
            $('#fecha_fin_provincia').val('');
            cargarProvinciasDesdeAPI();
        });

        $('#descargar-excel-provincia').click(function() {
            toast('Descargando reporte detallado de Provincias en Excel...', 'info');
        });

        // Handlers del tab General se gestionan dentro de su propio componente

        // Botones de descarga Excel
        $('#descargar-excel-visitadora').click(function() {
            toast('Descargando reporte detallado de Visitadoras en Excel...', 'info');
        });

        $('#descargar-excel-producto').click(function() {
            toast('Descargando reporte detallado de Productos en Excel...', 'info');
        });

        // Evento para abrir modal de pedidos detallados por departamento
        $(document).on('click', '.ver-pedidos-departamento', function() {
            const departamento = $(this).data('departamento');
            abrirModalPedidosDepartamento(departamento);
        });

        // Función para abrir modal y cargar pedidos detallados
        function abrirModalPedidosDepartamento(departamento) {
            // Configurar título del modal
            $('#modalDepartamentoNombre').text(departamento);

            // Resetear contenido del modal
            $('#modalTotalPedidos').text('0');
            $('#modalTotalVentas').text('S/ 0.00');
            $('#modalPromedioVenta').text('S/ 0.00');
            $('#tablaPedidosDetallados').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando pedidos...</p>
                        </td>
                    </tr>
                `);

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalPedidosDepartamento'));
            modal.show();

            // Obtener filtros actuales
            const filtros = {
                departamento: departamento,
                fecha_inicio_provincia: $('#fecha_inicio_provincia').val(),
                fecha_fin_provincia: $('#fecha_fin_provincia').val(),
                anio_general: $('#anio_general').val(),
                mes_general: $('#mes_general').val(),
                agrupacion: 'departamento'
            };

            // Realizar petición AJAX
            $.ajax({
                url: API_PEDIDOS_DEPARTAMENTO,
                type: 'GET',
                data: filtros,
                success: function(response) {
                    if (response.error) {
                        mostrarErrorEnModal(response.message || 'Error al cargar los datos');
                        return;
                    }

                    // Actualizar resumen
                    $('#modalTotalPedidos').text(response.total_pedidos || 0);
                    $('#modalTotalVentas').text('S/ ' + (response.total_ventas || 0).toLocaleString('es-PE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    const promedio = response.total_pedidos > 0 ? (response.total_ventas / response.total_pedidos) : 0;
                    $('#modalPromedioVenta').text('S/ ' + promedio.toLocaleString('es-PE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    // Generar tabla de pedidos
                    let tablaHtml = '';
                    if (response.pedidos && response.pedidos.length > 0) {
                        response.pedidos.forEach(function(pedido) {
                            const fecha = new Date(pedido.fecha_pedido).toLocaleDateString('es-PE');
                            const estado = pedido.total > 0 ? '<span class="badge bg-success">Completado</span>' : '<span class="badge bg-warning">Pendiente</span>';

                            tablaHtml += `
                                    <tr>
                                        <td>#${pedido.id}</td>
                                        <td>${fecha}</td>
                                        <td class="text-end">S/ ${pedido.total.toLocaleString('es-PE', {minimumFractionDigits: 2})}</td>
                                        <td>${pedido.distrito_original}</td>
                                        <td>${pedido.visitadora}</td>
                                        <td>${estado}</td>
                                    </tr>
                                `;
                        });
                    } else {
                        tablaHtml = `
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                        <p>No se encontraron pedidos para <strong>${departamento}</strong></p>
                                    </td>
                                </tr>
                            `;
                    }

                    $('#tablaPedidosDetallados').html(tablaHtml);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar pedidos:', error);
                    mostrarErrorEnModal('Error de conexión al servidor');
                }
            });
        }

        function mostrarErrorEnModal(mensaje) {
            $('#tablaPedidosDetallados').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p><strong>Error:</strong> ${mensaje}</p>
                        </td>
                    </tr>
                `);
        }

        // Evento para exportar pedidos a Excel
        $('#exportarPedidosDepartamento').click(function() {
            const departamento = $('#modalDepartamentoNombre').text();
            toast(`Próximamente: Exportar pedidos de ${departamento} a Excel`, 'info');
        });

        $('#descargar-excel-general').click(function() {
            toast('Descargando reporte general detallado en Excel...', 'info');
        });

        $('#filtrar_producto').click(function() {
            const inicio = $('#fecha_inicio_producto').val();
            const fin = $('#fecha_fin_producto').val();

            if (inicio && fin) {
                toast('Filtrando productos: ' + inicio + ' → ' + fin, 'success');
                // Aquí actualizarías los gráficos con datos filtrados
            } else {
                toast('Selecciona ambas fechas para productos', 'warning');
            }
        });

        $('#limpiar_producto').click(function() {
            $('#fecha_inicio_producto').val('');
            $('#fecha_fin_producto').val('');
            toast('Filtros de productos limpiados', 'info');
        });
    });

    function crearGraficoVisitadoras() {
        const ctx = document.getElementById('ventasChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosVentas.visitadoras.labels,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: datosVentas.visitadoras.datos,
                    backgroundColor: datosVentas.visitadoras.colores,
                    borderColor: datosVentas.visitadoras.colores,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Visitadora'
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
        console.log('Gráfico de visitadoras creado');
    }

    function crearGraficoPieVisitadoras() {
        const ctx = document.getElementById('ventasPieChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: datosVentas.visitadoras.labels,
                datasets: [{
                    data: datosVentas.visitadoras.datos,
                    backgroundColor: datosVentas.visitadoras.colores,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Ventas'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': S/ ' + context.parsed;
                            }
                        }
                    }
                }
            }
        });
        console.log('Gráfico pie de visitadoras creado');
    }

    function crearGraficoProductos() {
        const ctx = document.getElementById('productosChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosVentas.productos.labels,
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: datosVentas.productos.datos,
                    backgroundColor: datosVentas.productos.colores,
                    borderColor: datosVentas.productos.colores,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Producto'
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
        console.log('Gráfico de productos creado');
    }

    let provinciaBarChart = null;
    let provinciaPieChart = null;

    async function cargarProvinciasDesdeAPI() {
        const inicio = $('#fecha_inicio_provincia').val();
        const fin = $('#fecha_fin_provincia').val();
        const params = new URLSearchParams();

        // Importante: el backend espera 'fecha_inicio_provincia' y 'fecha_fin_provincia'
        if (inicio) params.append('fecha_inicio_provincia', inicio);
        if (fin) params.append('fecha_fin_provincia', fin);
        params.append('agrupacion', 'departamento');

        // Estado de carga
        $('#tablaProvinciaBody').html(
            '<tr><td colspan="4" class="text-center py-4 text-muted">Cargando...</td></tr>');
        $('#filtrar_provincia').prop('disabled', true);

        try {
            const url = `${API_VENTAS_PROVINCIAS}?${params.toString()}`;
            const resp = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!resp.ok) {
                throw new Error(`HTTP ${resp.status}`);
            }

            const data = await resp.json();
            renderProvincias(data);

        } catch (e) {
            $('#tablaProvinciaBody').html(
                '<tr><td colspan="4" class="text-center py-4 text-danger">Sin datos disponibles</td></tr>');
            Swal.fire({
                icon: 'error',
                title: 'Error al cargar datos',
                text: e.message || 'Ocurrió un problema al consultar el servidor',
                confirmButtonText: 'Reintentar'
            });
        } finally {
            $('#filtrar_provincia').prop('disabled', false);
        }
    }


    function renderProvincias(data) {
        // Header label
        $('#thGeoCol').text('Departamento');
        $('#tituloBarGeo').text('Departamento');
        $('#tituloPieGeo').text('Departamento');
        $('#tituloTablaGeo').text('Departamento');
        // Tabla: construir en frontend
        const labels = data.labels || [];
        const ventas = data.ventas || [];
        const porcentaje = data.porcentaje || [];
        const pedidos = data.pedidos || [];
        let tbody = '';
        if (labels.length === 0) {
            tbody = '<tr><td colspan="5" class="text-center py-4 text-muted">Sin datos para el rango seleccionado</td></tr>';
        } else {
            for (let i = 0; i < labels.length; i++) {
                const ventaVal = Number(ventas[i] || 0);
                const porcVal = Number(porcentaje[i] || 0);
                const pedidosVal = Number(pedidos[i] || 0);
                tbody += `
                        <tr>
                            <td>${labels[i] ?? ''}</td>
                            <td class="text-end">S/ ${ventaVal.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-center">${porcVal.toFixed(1)}%</td>
                            <td class="text-center">${pedidosVal}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary ver-pedidos-departamento" data-departamento="${labels[i] ?? ''}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>`;
            }
        }
        $('#tablaProvinciaBody').html(tbody);
        $('#totalVentasProvincia').text('S/ ' + (Number(data.total_ventas || 0).toLocaleString('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })));
        $('#totalPedidosProvincia').text(data.total_pedidos || 0);

        // Gráfico barras
        const barCtx = document.getElementById('provinciaChart');
        if (barCtx) {
            if (provinciaBarChart) provinciaBarChart.destroy();
            provinciaBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Ventas (S/)',
                        data: data.ventas || [],
                        backgroundColor: '#17a2b8',
                        borderColor: '#117a8b',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: data.titulo || 'Ventas por Ubigeo'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => 'S/ ' + v
                            }
                        }
                    }
                }
            });
        }

        // Gráfico pie
        const pieCtx = document.getElementById('provinciaPieChart');
        if (pieCtx) {
            if (provinciaPieChart) provinciaPieChart.destroy();
            const colors = (data.labels || []).map((_, i) => `hsl(${(i*37)%360} 70% 55%)`);
            provinciaPieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.ventas || [],
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribución de Ventas'
                        },
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: S/ ${ctx.parsed}`
                            }
                        }
                    }
                }
            });
        }
    }

    function crearGraficoReportesMes() {
        const ctx = document.getElementById('reportesMesChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosVentas.reportesMes.labels,
                datasets: [{
                    label: 'Ventas por Mes (S/)',
                    data: datosVentas.reportesMes.datos,
                    backgroundColor: datosVentas.reportesMes.colores,
                    borderColor: datosVentas.reportesMes.colores,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Reportes de Ventas por Mes'
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
        console.log('Gráfico de reportes por mes creado');
    }

    function crearGraficoVentasDia() {
        const ctx = document.getElementById('ventasDiaChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: datosVentas.ventasDia.labels,
                datasets: [{
                    label: 'Ventas por Día (S/)',
                    data: datosVentas.ventasDia.datos,
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
                        text: 'Tendencia de Ventas Diarias'
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
        console.log('Gráfico de ventas por día creado');
    }

    function crearGraficoTendencia() {
        const ctx = document.getElementById('tendenciaChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: datosVentas.tendencia.labels,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: datosVentas.tendencia.datos,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
                        text: 'Tendencia Mensual de Ventas'
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
        console.log('Gráfico de tendencia creado');
    }
</script>
@endsection