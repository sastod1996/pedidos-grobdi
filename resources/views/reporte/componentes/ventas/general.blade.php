<!-- Tab General -->
<div class="tab-pane fade" id="general" role="tabpanel">
    <h4 class="mb-4">
        <i class="fas fa-chart-bar text-warning"></i> Reporte General
    </h4>

    <!-- Filtros por Mes y Año -->
    <div class="row mb-4 p-3 bg-light rounded">
        <div class="col-12 col-sm-6 col-md-3 mb-3">
            <label for="mes_general" class="form-label">Mes</label>
            <select class="form-control" id="mes_general">
                <option value="">Todos los meses</option>
                <option value="01">Enero</option>
                <option value="02">Febrero</option>
                <option value="03">Marzo</option>
                <option value="04">Abril</option>
                <option value="05">Mayo</option>
                <option value="06">Junio</option>
                <option value="07">Julio</option>
                <option value="08">Agosto</option>
                <option value="09">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3">
            <label for="anio_general" class="form-label">Año</label>
            <select class="form-control" id="anio_general">
                @php
                $currentYear = date('Y');
                // Mostrar años desde el actual hasta 2020 en orden descendente
                for ($year = $currentYear; $year >= 2020; $year--) {
                $selected = ($year == $currentYear) ? 'selected' : '';
                echo "<option value=\"$year\" $selected>$year</option>";
                }
                @endphp
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3">
            <label>&nbsp;</label><br>
            <button class="btn btn-primary btn-block w-100" id="filtrar_general">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3">
            <label>&nbsp;</label><br>
            <button class="btn btn-secondary btn-block w-100" id="limpiar_general">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Métricas generales -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-lg-4 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <h3>S/ {{ number_format($data['general']['total_ventas'] ?? 0, 2) }}</h3>
                    <p class="mb-0">Ingresos Totales</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <h3>{{ $data['general']['total_visitas'] ?? 0 }}</h3>
                    <p class="mb-0">Total Visitas</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 mb-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body text-center">
                    <h3>S/ {{ number_format($data['general']['promedio_venta'] ?? 0, 2) }}</h3>
                    <p class="mb-0">Ingreso/Visita</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas de Reportes por Mes y Ventas por Día -->
    <div class="row mb-4">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Reportes por Mes
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="reportesMesChart" style="height: 300px; max-height: 60vh;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Ventas por Día
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="ventasDiaChart" style="height: 300px; max-height: 60vh;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de tendencia -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Tendencia de Ventas Mensual</h5>
                </div>
                <div class="card-body">
                    <canvas id="tendenciaChart" style="height: 400px; max-height: 70vh;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Descargar Excel -->
    <div class="text-center mt-4">
        <button class="btn btn-success btn-lg" id="descargar-excel-general">
            <i class="fas fa-download"></i> <span class="d-none d-sm-inline">Descargar Detallado Excel</span>
            <span class="d-sm-none">Descargar Excel</span>
        </button>
    </div>
</div>
<script src="{{ asset('js/chart-factory.js') }}"></script>
<script>
    // Esperar a que jQuery esté disponible
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar que jQuery esté cargado
        if (typeof $ === 'undefined') {
            console.error('jQuery no está cargado');
            return;
        }

        // Agregar estilos responsivos para los gráficos
        const style = document.createElement('style');
        style.textContent = `
        @media (max-width: 768px) {
            .card-body canvas {
                height: 250px !important;
                max-height: 50vh !important;
            }
            .card h5 {
                font-size: 1.1rem;
            }
            .card h3 {
                font-size: 1.5rem;
            }
        }
        @media (max-width: 576px) {
            .card-body canvas {
                height: 200px !important;
                max-height: 40vh !important;
            }
            .card h5 {
                font-size: 1rem;
            }
            .card h3 {
                font-size: 1.3rem;
            }
            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }
    `;
        document.head.appendChild(style);

        // Variables globales para gráficos
        let chartMes, chartDia, chartTendencia;

        // Datos iniciales del backend
        let generalData = @json($data['general'] ?? []);
        console.log('Datos iniciales:', generalData);

        // Inicializar todo al cargar la página
        if (generalData && Object.keys(generalData).length > 0) {
            inicializarGraficos(generalData);
            actualizarMetricas(generalData);
        }

        // Event listeners para los botones
        $(document).on('click', '#filtrar_general', function(e) {
            e.preventDefault();
            // Evita que otros handlers (definidos en la vista principal) disparen alerts duplicados
            e.stopImmediatePropagation();
            aplicarFiltros();
        });

        $(document).on('click', '#limpiar_general', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            limpiarFiltros();
        });

        // Interceptar descarga Excel para evitar alert del script padre
        $(document).on('click', '#descargar-excel-general', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toast('Descargando reporte general (próximamente)', 'info');
        });

        // Función para aplicar filtros
        function aplicarFiltros() {
            const mes = $('#mes_general').val();
            const anio = $('#anio_general').val();

            // Validar que al menos el año esté seleccionado
            if (!anio) {
                toast('Por favor seleccione al menos un año');
                return;
            }

            // Mostrar loading
            $('#filtrar_general').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            // Petición AJAX al backend
            $.ajax({
                url: '{{ route("api.reportes.ventas-general") }}',
                method: 'GET',
                data: {
                    mes_general: mes || '',
                    anio_general: anio || ''
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta del servidor:', response);

                    if (response && response.general) {
                        // Actualizar datos
                        generalData = response.general;

                        // Actualizar métricas
                        actualizarMetricas(response.general);

                        // Destruir gráficos existentes y crear nuevos
                        destruirGraficos();
                        inicializarGraficos(response.general);
                    } else {
                        toast('No se recibieron datos válidos del servidor');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la petición:', error);
                    toast('Error al cargar los datos. Intente nuevamente.');
                },
                complete: function() {
                    $('#filtrar_general').prop('disabled', false).html('<i class="fas fa-filter"></i> Filtrar');
                }
            });
        }

        // Función para limpiar filtros
        function limpiarFiltros() {
            $('#mes_general').val('');
            $('#anio_general').val('{{ date("Y") }}');
            aplicarFiltros();
        }

        // Función para actualizar las métricas
        function actualizarMetricas(data) {
            const totalVentas = data.total_ventas || 0;
            const totalVisitas = data.total_visitas || 0;
            const promedioVenta = data.promedio_venta || 0;

            $('#general .card.bg-warning h3').text('S/ ' + totalVentas.toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('#general .card.bg-danger h3').text(totalVisitas.toLocaleString());

            $('#general .card.bg-dark h3').text('S/ ' + promedioVenta.toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        // Función para destruir gráficos existentes
        function destruirGraficos() {
            if (chartMes) {
                chartMes.destroy();
                chartMes = null;
            }
            if (chartDia) {
                chartDia.destroy();
                chartDia = null;
            }
            if (chartTendencia) {
                chartTendencia.destroy();
                chartTendencia = null;
            }
        }

        // Función para inicializar gráficos
        function inicializarGraficos(data) {
            console.log('Inicializando gráficos con data:', data);

            if (!data || !data.labels || !data.ventas) {
                console.warn('Datos insuficientes para crear gráficos');
                return;
            }

            // Mostrar/ocultar gráficos según el tipo de datos
            if (data.tipo === 'diario') {
                // Mostrar solo gráfico diario
                $('#reportesMesChart').parent().parent().hide();
                $('#tendenciaChart').parent().parent().hide();
                $('#ventasDiaChart').parent().parent().show();

                crearGraficoDiario(data);
            } else {
                // Mostrar gráficos mensuales
                $('#ventasDiaChart').parent().parent().hide();
                $('#reportesMesChart').parent().parent().show();
                $('#tendenciaChart').parent().parent().show();

                crearGraficoMensual(data);
                crearGraficoTendencia(data);
            }
        }

        // Crear gráfico mensual
        function crearGraficoMensual(data) {
            const labels = data.labels || [];
            const ventas = data.ventas || [];

            if (!labels.length || !ventas.length) return;
            const datasets = [{
                label: 'Ventas (S/)',
                data: ventas,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }];
            const extraOptions = {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: data.periodo || 'Ventas por Mes',
                        font: {
                            size: window.innerWidth < 768 ? 14 : 16
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: window.innerWidth < 768 ? 12 : 14
                            }
                        }
                    }
                }
            };
            chartMes = createChart('#reportesMesChart', labels, datasets, 'bar', extraOptions);
        }

        function crearGraficoTendencia(data) {
            const labels = data.labels || [];
            const ventas = data.ventas || [];

            if (!labels.length || !ventas.length) return;

            const datasets = [{
                label: 'Tendencia de Ventas',
                data: ventas,
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }];

            const extraOptions = {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Tendencia de Ventas',
                        font: {
                            size: window.innerWidth < 768 ? 14 : 16
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: window.innerWidth < 768 ? 12 : 14
                            }
                        }
                    }
                }
            };

            chartTendencia = createChart('#tendenciaChart', labels, datasets, 'line', extraOptions);
        }

        function crearGraficoDiario(data) {
            const labels = data.labels || [];
            const ventas = data.ventas || [];

            if (!labels.length || !ventas.length) return;

            const datasets = [{
                label: 'Ventas Diarias (S/)',
                data: ventas,
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }];

            const extraOptions = {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: window.innerWidth < 768 ? 7 : 15
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: data.periodo || 'Ventas Diarias',
                        font: {
                            size: window.innerWidth < 768 ? 14 : 16
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: window.innerWidth < 768 ? 12 : 14
                            }
                        }
                    }
                }
            };

            chartDia = createChart('#ventasDiaChart', labels, datasets, 'line', extraOptions);
        }

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
    });
</script>