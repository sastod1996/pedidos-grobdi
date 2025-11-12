@extends('adminlte::page')

@section('title', 'Bonificaciones - Meta Visitadora')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pagination {
            gap: 0.25rem;
        }

        .page-link {
            border-radius: 0.375rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            margin: 0 2px;
        }

        .page-link:hover:not(.disabled) {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
        }

        .page-item.disabled .page-link {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        #doctorsTableInfo,
        #metaDataTableInfo {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
        }
    </style>
@endsection

@section('content')
    @php($currentMonth = now()->month)

    <section class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h1>Meta de Visitadora</h1>
                <p class="mb-0">Consulta tu meta mensual y monitorea el avance de tus pedidos.</p>
            </div>
        </div>

        <div class="grobdi-filter">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label for="monthSelect" class="grobdi-label mb-1">Mes</label>
                    <select id="monthSelect" class="grobdi-input">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create(null, $m)->locale('es')->translatedFormat('F')) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="yearSelect" class="grobdi-label mb-1">Año</label>
                    <select id="yearSelect" class="grobdi-input">
                        @php($currentYear = now()->year)
                        @for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-12 col-md-3">
                    <button id="btnConsultar" class="btn-grobdi btn-primary-grobdi w-100">
                        <i class="fas fa-search"></i> Consultar meta
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div id="inlineAlert" class="alert alert-grobdi alert-info-grobdi d-none mb-4" role="alert"></div>

    <section id="resultsSection" class="mt-4">
        <h2 class="h4 mb-3 fw-bold text-uppercase" id="resultsTitle">
            Resultados de la Meta
            <span class="text-muted small fw-normal" id="periodLabel"></span>
        </h2>

        <div class="row">
            <div class="col-lg-5 mb-3">
                <div class="card-grobdi card h-100">
                    <div class="card-header-grobdi">
                        <i class="fas fa-bullseye me-2"></i>Resumen de Meta
                    </div>
                    <div class="card-body card-body-grobdi" id="metaSummary">
                        <div class="text-muted text-center py-4">
                            <i class="fas fa-chart-line fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Sin datos disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-3">
                <div class="card-grobdi card h-100">
                    <div class="card-header-grobdi d-flex justify-content-between align-items-center">
                        <strong><i class="fas fa-chart-bar me-2"></i>Progreso Visual</strong>
                        <small class="text-muted" id="chartHint"></small>
                    </div>
                    <div class="card-body card-body-grobdi" style="height: 420px;">
                        <canvas id="chartCanvas"></canvas>
                        <div class="text-center text-muted py-5 d-none" id="chartEmptyState">
                            <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No hay datos para mostrar en el gráfico</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <div class="card-grobdi card">
                    <div class="card-header-grobdi d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-md me-2"></i>Médicos / Pedidos
                        </div>
                        <div id="doctorsTableInfo" class="small text-muted"></div>
                    </div>
                    <div class="card-body card-body-grobdi p-0">
                        <div class="table-responsive">
                            <table class="table table-grobdi table-sm table-striped mb-0" id="doctorsTable">
                                <thead></thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i>Cargando datos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="doctorsPagination" class="d-flex justify-content-center align-items-center p-3 border-top"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-2">
                <div class="card-grobdi card">
                    <div class="card-header-grobdi d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list-ul me-2"></i>Metas Relacionadas
                        </div>
                        <div id="metaDataTableInfo" class="small text-muted"></div>
                    </div>
                    <div class="card-body card-body-grobdi p-0">
                        <div class="table-responsive">
                            <table class="table table-grobdi table-sm table-hover mb-0" id="metaDataTable">
                                <thead></thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i>Cargando datos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="metaDataPagination" class="d-flex justify-content-center align-items-center p-3 border-top"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    <script>
        const endpoint = "{{ route('visitadoras.metas.show.logged') }}";
        let chartInstance = null;

        // Variables para paginación
        let doctorsDataFull = [];
        let metaDataFull = [];
        let doctorsCurrentPage = 1;
        let metaDataCurrentPage = 1;
        const itemsPerPage = 20;

        // Paleta de colores Grobdi
        const grobdiColors = {
            primary: '#dc2626', // Rojo principal
            primaryLight: '#ef4444',
            primaryDark: '#b91c1c',
            navy: '#1e293b',
            navyLight: '#334155',
            slate: '#64748b',
            slateLight: '#cbd5e1',
            success: '#10b981',
            warning: '#f59e0b',
            info: '#3b82f6',
        };

        function showInlineAlert(message, type = 'info') {
            const el = document.getElementById('inlineAlert');
            if (!el) return;
            const classMap = {
                info: 'alert-info-grobdi',
                success: 'alert-success-grobdi',
                warning: 'alert-warning-grobdi',
                danger: 'alert-danger-grobdi'
            };
            const tone = classMap[type] ?? classMap.info;
            el.className = `alert alert-grobdi ${tone} mb-4`;
            el.innerHTML = `<i class="fas fa-info-circle me-2"></i>${message || ''}`;
            el.classList.remove('d-none');
        }

        function hideInlineAlert() {
            const el = document.getElementById('inlineAlert');
            if (!el) return;
            el.classList.add('d-none');
            el.innerHTML = '';
        }

        function showLoading(button, loading = true) {
            if (!button) return;
            if (loading) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...';
                button.disabled = true;
            } else {
                button.innerHTML = button.dataset.originalText || '<i class="fas fa-search"></i> Consultar meta';
                button.disabled = false;
            }
        }

        function prettifyKey(key) {
            const translations = {
                'id': 'ID',
                'tipo_medico': 'Tipo de Médico',
                'tipo_medico_label': 'Tipo de Médico',
                'start_date': 'Fecha Inicio',
                'end_date': 'Fecha Fin',
                'month': 'Mes',
                'period_label': 'Período',
                'total_pedidos': 'Total Pedidos',
                'total_amount_without_igv': 'Total sin IGV (S/)',
                'faltante_para_meta': 'Faltante para Meta (S/)',
                'avance_meta_general': 'Avance (%)',
                'commissioned_amount': 'Monto Comisionado (S/)',
                'commission_rate': 'Tasa de Comisión (%)',
                'debited_amount': 'Monto Debitado (S/)',
                'visitadora': 'Visitadora',
                'name': 'Nombre',
                'commission_percentage': 'Comisión Base (%)',
                'goal_amount': 'Meta Objetivo (S/)',
                'porcentaje_actual': 'Porcentaje Actual (%)',
                'comision_actual': 'Comisión Actual (%)',
                'total_sub_total_sin_igv': 'Total sin IGV (S/)',
                'monto_comisionado': 'Monto Comisionado (S/)',
                'debited_datetime': 'Fecha de Débito',
            };

            return translations[key] || String(key)
                .replaceAll('_', ' ')
                .replace(/\b\w/g, c => c.toUpperCase());
        }

        function formatValue(value, key = '') {
            if (value === null || value === undefined) return '-';

            // Si es un objeto, intentar extraer información útil
            if (typeof value === 'object' && !Array.isArray(value)) {
                if (value.name) return value.name;
                if (value.label) return value.label;
                return JSON.stringify(value);
            }

            // Formatear números con decimales
            if (typeof value === 'number' || (!isNaN(value) && value !== '')) {
                const num = parseFloat(value);
                if (key.includes('amount') || key.includes('monto') || key.includes('faltante')) {
                    return 'S/ ' + num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                if (key.includes('percentage') || key.includes('porcentaje') || key.includes('comision') || key.includes('rate')) {
                    return num.toFixed(2) + '%';
                }
                return num.toLocaleString('es-PE');
            }

            return value;
        }

        function renderSummary(chartData, meta) {
            const container = document.getElementById('metaSummary');
            const periodLabel = document.getElementById('periodLabel');

            container.innerHTML = '';

            if (!chartData || typeof chartData !== 'object') {
                container.innerHTML = '<div class="text-muted text-center py-4"><i class="fas fa-chart-line fa-3x mb-3 opacity-25"></i><p class="mb-0">Sin datos disponibles</p></div>';
                periodLabel.textContent = '';
                return;
            }

            // Actualizar el título con el período
            if (meta && meta.period_label) {
                periodLabel.textContent = `(${meta.period_label})`;
            }

            // Crear cards de métricas con íconos
            const metrics = [
                { key: 'total_pedidos', icon: 'fa-shopping-cart', color: grobdiColors.info },
                { key: 'total_amount_without_igv', icon: 'fa-dollar-sign', color: grobdiColors.success },
                { key: 'faltante_para_meta', icon: 'fa-flag-checkered', color: grobdiColors.warning },
                { key: 'avance_meta_general', icon: 'fa-chart-line', color: grobdiColors.primary },
                { key: 'commissioned_amount', icon: 'fa-coins', color: grobdiColors.success },
                { key: 'commission_rate', icon: 'fa-percent', color: grobdiColors.navyLight },
            ];

            let html = '<div class="row g-3">';

            metrics.forEach(metric => {
                if (chartData.hasOwnProperty(metric.key)) {
                    const value = formatValue(chartData[metric.key], metric.key);
                    const label = prettifyKey(metric.key);

                    html += `
                        <div class="col-12">
                            <div class="d-flex align-items-center p-3 border rounded" style="border-left: 4px solid ${metric.color} !important; background-color: #f8fafc;">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fas ${metric.icon} fa-2x" style="color: ${metric.color};"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">${label}</div>
                                    <div class="fs-5 fw-bold" style="color: ${grobdiColors.navy};">${value}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            html += '</div>';
            container.innerHTML = html;
        }

        function renderTable(data, tableEl) {
            const thead = tableEl.querySelector('thead');
            const tbody = tableEl.querySelector('tbody');
            thead.innerHTML = '';
            tbody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="100%" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No hay datos para mostrar.</td></tr>';
                return;
            }

            // Preparar datos aplanados
            const flattenedData = data.map(row => {
                const flatRow = {};
                Object.keys(row).forEach(k => {
                    if (typeof row[k] === 'object' && row[k] !== null && !Array.isArray(row[k])) {
                        // Si es un objeto, extraer propiedades
                        Object.keys(row[k]).forEach(subK => {
                            flatRow[`${k}_${subK}`] = row[k][subK];
                        });
                    } else {
                        flatRow[k] = row[k];
                    }
                });
                return flatRow;
            });

            const keys = Object.keys(flattenedData[0] ?? {});
            const headRow = document.createElement('tr');
            keys.forEach(k => {
                const th = document.createElement('th');
                th.textContent = prettifyKey(k);
                th.className = 'text-uppercase small fw-semibold';
                th.style.backgroundColor = grobdiColors.navy;
                th.style.color = '#fff';
                headRow.appendChild(th);
            });
            thead.appendChild(headRow);

            flattenedData.forEach((row, idx) => {
                const tr = document.createElement('tr');
                if (idx % 2 === 0) {
                    tr.style.backgroundColor = '#f8fafc';
                }
                keys.forEach(k => {
                    const td = document.createElement('td');
                    let val = row[k];
                    td.textContent = formatValue(val, k);
                    td.className = 'align-middle';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        function paginateData(data, page, perPage) {
            const start = (page - 1) * perPage;
            const end = start + perPage;
            return data.slice(start, end);
        }

        function renderPagination(totalItems, currentPage, paginationEl, infoEl, onPageChange) {
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            // Actualizar información
            if (infoEl) {
                const start = totalItems > 0 ? ((currentPage - 1) * itemsPerPage) + 1 : 0;
                const end = Math.min(currentPage * itemsPerPage, totalItems);
                infoEl.textContent = `Mostrando ${start} - ${end} de ${totalItems}`;
            }

            paginationEl.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            const nav = document.createElement('nav');
            nav.setAttribute('aria-label', 'Paginación de tabla');

            const ul = document.createElement('ul');
            ul.className = 'pagination pagination-sm mb-0';

            // Botón Anterior
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.href = '#';
            prevLink.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevLink.style.color = grobdiColors.navy;
            prevLink.onclick = (e) => {
                e.preventDefault();
                if (currentPage > 1) onPageChange(currentPage - 1);
            };
            prevLi.appendChild(prevLink);
            ul.appendChild(prevLi);

            // Lógica de páginas a mostrar
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

            // Ajustar si estamos cerca del inicio o fin
            if (currentPage <= 3) {
                endPage = Math.min(5, totalPages);
            }
            if (currentPage >= totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
            }

            // Primera página
            if (startPage > 1) {
                const li = createPageItem(1, currentPage, onPageChange);
                ul.appendChild(li);
                if (startPage > 2) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = '<span class="page-link">...</span>';
                    ul.appendChild(dotsLi);
                }
            }

            // Páginas numeradas
            for (let i = startPage; i <= endPage; i++) {
                const li = createPageItem(i, currentPage, onPageChange);
                ul.appendChild(li);
            }

            // Última página
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = '<span class="page-link">...</span>';
                    ul.appendChild(dotsLi);
                }
                const li = createPageItem(totalPages, currentPage, onPageChange);
                ul.appendChild(li);
            }

            // Botón Siguiente
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            const nextLink = document.createElement('a');
            nextLink.className = 'page-link';
            nextLink.href = '#';
            nextLink.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextLink.style.color = grobdiColors.navy;
            nextLink.onclick = (e) => {
                e.preventDefault();
                if (currentPage < totalPages) onPageChange(currentPage + 1);
            };
            nextLi.appendChild(nextLink);
            ul.appendChild(nextLi);

            nav.appendChild(ul);
            paginationEl.appendChild(nav);
        }

        function createPageItem(pageNum, currentPage, onPageChange) {
            const li = document.createElement('li');
            li.className = `page-item ${pageNum === currentPage ? 'active' : ''}`;
            const link = document.createElement('a');
            link.className = 'page-link';
            link.href = '#';
            link.textContent = pageNum;

            if (pageNum === currentPage) {
                link.style.backgroundColor = grobdiColors.primary;
                link.style.borderColor = grobdiColors.primary;
                link.style.color = '#fff';
            } else {
                link.style.color = grobdiColors.navy;
            }

            link.onclick = (e) => {
                e.preventDefault();
                if (pageNum !== currentPage) onPageChange(pageNum);
            };
            li.appendChild(link);
            return li;
        }

        function renderDoctorsTable(page = 1) {
            doctorsCurrentPage = page;
            const paginatedData = paginateData(doctorsDataFull, page, itemsPerPage);
            const tableEl = document.getElementById('doctorsTable');
            renderTable(paginatedData, tableEl);
            renderPagination(
                doctorsDataFull.length,
                page,
                document.getElementById('doctorsPagination'),
                document.getElementById('doctorsTableInfo'),
                renderDoctorsTable
            );
        }

        function renderMetaDataTable(page = 1) {
            metaDataCurrentPage = page;
            const paginatedData = paginateData(metaDataFull, page, itemsPerPage);
            const tableEl = document.getElementById('metaDataTable');
            renderTable(paginatedData, tableEl);
            renderPagination(
                metaDataFull.length,
                page,
                document.getElementById('metaDataPagination'),
                document.getElementById('metaDataTableInfo'),
                renderMetaDataTable
            );
        }

        function renderChart(chartData) {
            const canvas = document.getElementById('chartCanvas');
            const emptyState = document.getElementById('chartEmptyState');
            const hint = document.getElementById('chartHint');

            hint.textContent = '';
            emptyState.classList.add('d-none');
            canvas.classList.remove('d-none');

            if (!chartData || typeof chartData !== 'object') {
                canvas.classList.add('d-none');
                emptyState.classList.remove('d-none');
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
                return;
            }

            // Crear gráfico de progreso
            const labels = ['Alcanzado', 'Faltante'];
            const totalAmount = parseFloat(chartData.total_amount_without_igv || 0);
            const faltante = parseFloat(chartData.faltante_para_meta || 0);

            const data = {
                labels: labels,
                datasets: [{
                    label: 'Monto (S/)',
                    data: [totalAmount, faltante],
                    backgroundColor: [
                        grobdiColors.success,
                        grobdiColors.slateLight,
                    ],
                    borderColor: [
                        grobdiColors.success,
                        grobdiColors.slate,
                    ],
                    borderWidth: 2
                }]
            };

            const config = {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 14,
                                    family: "'Nunito', sans-serif",
                                    weight: 600
                                },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        title: {
                            display: true,
                            text: `Avance: ${chartData.avance_meta_general || 0}%`,
                            font: {
                                size: 18,
                                family: "'Nunito', sans-serif",
                                weight: 700
                            },
                            color: grobdiColors.navy,
                            padding: 20
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'S/ ' + context.parsed.toLocaleString('es-PE', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                    return label;
                                }
                            }
                        }
                    }
                }
            };

            if (chartInstance) chartInstance.destroy();
            chartInstance = new Chart(canvas.getContext('2d'), config);
            hint.textContent = 'Gráfico generado automáticamente';
        }

        function clearUI() {
            renderSummary(null, null);

            // Limpiar datos y tablas
            doctorsDataFull = [];
            metaDataFull = [];
            doctorsCurrentPage = 1;
            metaDataCurrentPage = 1;

            renderTable([], document.getElementById('doctorsTable'));
            renderTable([], document.getElementById('metaDataTable'));
            document.getElementById('doctorsPagination').innerHTML = '';
            document.getElementById('metaDataPagination').innerHTML = '';
            document.getElementById('doctorsTableInfo').textContent = '';
            document.getElementById('metaDataTableInfo').textContent = '';

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
            document.getElementById('chartCanvas').classList.add('d-none');
            document.getElementById('chartEmptyState').classList.remove('d-none');
        }

        async function consultar(buttonEl) {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;

            showLoading(buttonEl, true);
            hideInlineAlert();

            try {
                const url = `${endpoint}?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    clearUI();
                    const message = 'No hay meta activa para este mes y tipo de médico';
                    showInlineAlert(message, 'warning');
                    document.getElementById('resultsSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                const data = await res.json();
                if (!data.success) {
                    clearUI();
                    const message = data.message || 'No hay meta activa para este mes y tipo de médico';
                    showInlineAlert(message, 'warning');
                    document.getElementById('resultsSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                hideInlineAlert();

                // Guardar datos completos para paginación
                doctorsDataFull = Array.isArray(data["doctors-data"]) ? data["doctors-data"] : [];
                metaDataFull = Array.isArray(data["meta-data"]) ? data["meta-data"] : [];

                // Renderizar datos con paginación
                renderSummary(data["chart-data"], data.meta);
                renderDoctorsTable(1);
                renderMetaDataTable(1);
                renderChart(data["chart-data"]);

                // Desplazar a la sección de resultados
                document.getElementById('resultsSection')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            } catch (err) {
                console.error(err);
                clearUI();
                showInlineAlert('Ocurrió un error al consultar los datos. Por favor, intente nuevamente.', 'danger');
            } finally {
                showLoading(buttonEl, false);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btnConsultar');
            btn?.addEventListener('click', () => consultar(btn));

            // Consulta inicial al cargar la página
            consultar(btn);
        });
    </script>
@endsection
