(() => {
    const doctorNameCache = new Map();

    const initSeguimientoModule = () => {
        const filterForm = document.getElementById('seguimientoFilterForm');
        if (!filterForm) {
            return;
        }

        const endpointBase = filterForm.dataset.fetchEndpoint || '';
        if (!endpointBase) {
            console.warn('No se definió el endpoint para el reporte de seguimiento.');
            return;
        }

        if (typeof Chart === 'undefined') {
            console.warn('Chart.js no disponible para el comparativo de seguimiento.');
            return;
        }

        const cssRoot = getComputedStyle(document.documentElement);
        const colors = {
            amountPrimary: cssRoot.getPropertyValue('--grobdi-navy-900').trim() || '#0f172a',
            amountSecondary: cssRoot.getPropertyValue('--grobdi-navy-700').trim() || '#1e293b',
            quantityPrimary: cssRoot.getPropertyValue('--grobdi-red-500').trim() || '#ef4444',
            quantitySecondary: cssRoot.getPropertyValue('--grobdi-red-600').trim() || '#dc2626',
            grid: cssRoot.getPropertyValue('--grobdi-slate-200').trim() || '#e5e7eb',
            text: cssRoot.getPropertyValue('--grobdi-text-base').trim() || '#1f2937',
            muted: cssRoot.getPropertyValue('--grobdi-slate-400').trim() || '#9ca3af'
        };

        const hexToRGBA = (hex, alpha = 0.2) => {
            if (!hex) {
                return `rgba(15, 23, 42, ${alpha})`;
            }

            let sanitized = hex.trim();
            if (sanitized.startsWith('var(')) {
                return `rgba(15, 23, 42, ${alpha})`;
            }

            if (sanitized.startsWith('#')) {
                sanitized = sanitized.slice(1);
            }

            if (sanitized.length === 3) {
                sanitized = sanitized.split('').map(char => char + char).join('');
            }

            if (sanitized.length !== 6) {
                return `rgba(15, 23, 42, ${alpha})`;
            }

            const numeric = parseInt(sanitized, 16);
            const r = (numeric >> 16) & 255;
            const g = (numeric >> 8) & 255;
            const b = numeric & 255;

            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        };

        const currencyFormatter = new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            maximumFractionDigits: 0
        });

        const quantityFormatter = new Intl.NumberFormat('es-PE', {
            maximumFractionDigits: 0
        });

        const monthFormatter = new Intl.DateTimeFormat('es-PE', {
            month: 'short',
            year: 'numeric'
        });

        const parseDateValue = value => {
            if (!value) {
                return null;
            }

            if (typeof value === 'string' && /^\d{4}-\d{2}$/.test(value)) {
                return new Date(`${value}-01`);
            }

            const parsed = new Date(value);
            return Number.isNaN(parsed.getTime()) ? null : parsed;
        };

        const buildRangeLabel = (start, end) => {
            const startDate = parseDateValue(start);
            const endDate = parseDateValue(end);

            if (startDate && endDate) {
                return `${monthFormatter.format(startDate)} - ${monthFormatter.format(endDate)}`;
            }

            if (startDate) {
                return monthFormatter.format(startDate);
            }

            if (endDate) {
                return monthFormatter.format(endDate);
            }

            return '';
        };

        const formatSignedCurrency = value => {
            const numeric = Number(value) || 0;
            if (numeric === 0) {
                return currencyFormatter.format(0);
            }

            const prefix = numeric > 0 ? '+ ' : '- ';
            return `${prefix}${currencyFormatter.format(Math.abs(numeric))}`;
        };

        const formatSignedQuantity = value => {
            const numeric = Number(value) || 0;
            if (numeric === 0) {
                return '0';
            }

            const prefix = numeric > 0 ? '+ ' : '- ';
            return `${prefix}${quantityFormatter.format(Math.abs(numeric))}`;
        };

        const filterFormElements = {
            form: filterForm,
            rangeLabel: document.getElementById('seguimientoRangeLabel'),
            resetButton: document.getElementById('seguimientoReset'),
            metricButtons: document.querySelectorAll('[data-seguimiento-metric]'),
            metricStatus: document.getElementById('seguimientoMetricStatus'),
            orderButton: document.getElementById('order_table')
        };

        const defaultLabel = filterFormElements.rangeLabel?.dataset?.defaultLabel || '';

        const metricLabels = {
            amount: 'Montos',
            quantity: 'Cantidades'
        };

        const allowedFilterKeys = ['start_date_1', 'end_date_1', 'start_date_2', 'end_date_2'];

        const extractRequestFilters = (source = {}) => allowedFilterKeys.reduce((acc, key) => {
            const value = source?.[key];
            if (value) {
                acc[key] = value;
            }
            return acc;
        }, {});

        const metricSettings = {
            amount: {
                key: 'amount_diff',
                label: 'Variacion en montos (S/)',
                tooltipLabel: 'Monto',
                format: value => currencyFormatter.format(value)
            },
            quantity: {
                key: 'quantity_diff',
                label: 'Variacion en pedidos',
                tooltipLabel: 'Pedidos',
                format: value => `${quantityFormatter.format(value)} pedidos`
            }
        };

        const chartPalette = {
            positives: {
                amount: colors.amountPrimary,
                quantity: colors.quantityPrimary
            },
            negatives: {
                amount: colors.amountSecondary,
                quantity: colors.quantitySecondary
            }
        };

        const trendMetricSettings = {
            amount: {
                positiveKey: 'positive_amount',
                negativeKey: 'negative_amount',
                positiveLabel: 'Montos positivos',
                negativeLabel: 'Montos negativos',
                formatter: value => currencyFormatter.format(value),
                colors: {
                    positive: colors.amountPrimary,
                    negative: colors.amountSecondary
                }
            },
            quantity: {
                positiveKey: 'positive_quantity',
                negativeKey: 'negative_quantity',
                positiveLabel: 'Pedidos en incremento',
                negativeLabel: 'Pedidos en descenso',
                formatter: value => `${quantityFormatter.format(value)} pedidos`,
                colors: {
                    positive: colors.quantityPrimary,
                    negative: colors.quantitySecondary
                }
            }
        };

        const formatRange = (start, end) => {
            if (!start || !end) {
                return '';
            }

            const [startYear, startMonth] = start.split('-').map(Number);
            const [endYear, endMonth] = end.split('-').map(Number);

            const startDate = new Date(startYear, startMonth - 1);
            const endDate = new Date(endYear, endMonth - 1);

            return `${monthFormatter.format(startDate)} - ${monthFormatter.format(endDate)}`;
        };

        const getFirstDayOfMonth = value => {
            if (!value) {
                return null;
            }

            const [year, month] = value.split('-');
            if (!year || !month) {
                return null;
            }

            return `${year}-${month}-01`;
        };

        const getLastDayOfMonth = value => {
            if (!value) {
                return null;
            }

            const [yearRaw, monthRaw] = value.split('-').map(Number);
            if (!yearRaw || !monthRaw) {
                return null;
            }

            const date = new Date(yearRaw, monthRaw, 0);
            const day = String(date.getDate()).padStart(2, '0');
            return `${yearRaw}-${String(monthRaw).padStart(2, '0')}-${day}`;
        };

        const applyMetricToTables = metric => {
            document.querySelectorAll('[data-metric]').forEach(element => {
                element.hidden = element.dataset.metric !== metric;
            });
        };

        const updateMetricStatus = metric => {
            if (!filterFormElements.metricStatus) {
                return;
            }

            filterFormElements.metricStatus.textContent = metricLabels[metric] || '';
        };

        const setActiveMetricButton = metric => {
            filterFormElements.metricButtons.forEach(button => {
                const isActive = button.dataset.seguimientoMetric === metric;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        const updateHorizontalChart = (chart, dataset, metricKey, paletteKey) => {
            if (!chart || !metricSettings[metricKey]) {
                return;
            }

            const metric = metricSettings[metricKey];
            const values = dataset.map(item => Number(item[metric.key]) || 0);
            const labels = dataset.map(item => item.name);

            const minValue = Math.min(...values);
            const maxValue = Math.max(...values);
            const padding = (Math.abs(maxValue - minValue) || 1) * 0.15;

            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].label = metric.label;
            chart.data.datasets[0].backgroundColor = chartPalette[paletteKey][metricKey];

            chart.options.scales.x.min = minValue < 0 ? minValue - padding : 0;
            chart.options.scales.x.max = maxValue > 0 ? maxValue + padding : 0;
            chart.options.scales.x.ticks.callback = value => metric.format(value);
            chart.options.plugins.tooltip.callbacks.label = context =>
                `${metric.tooltipLabel}: ${metric.format(context.parsed.x)}`;

            chart.update();
        };

        const buildHorizontalChart = (canvasId, dataset, metricKey, paletteKey) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !metricSettings[metricKey]) {
                return null;
            }

            const metric = metricSettings[metricKey];

            const chart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dataset.map(item => item.name),
                    datasets: [{
                        label: metric.label,
                        data: dataset.map(item => Number(item[metric.key]) || 0),
                        backgroundColor: chartPalette[paletteKey][metricKey],
                        borderRadius: 8,
                        maxBarThickness: 34
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'nearest',
                        axis: 'y',
                        intersect: false
                    },
                    scales: {
                        x: {
                            grid: {
                                color: colors.grid,
                                drawTicks: false
                            },
                            ticks: {
                                color: colors.text,
                                callback: value => metric.format(value)
                            }
                        },
                        y: {
                            offset: true,
                            ticks: {
                                color: colors.text,
                                font: {
                                    weight: '600',
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${metric.tooltipLabel}: ${metric.format(context.parsed.x)}`
                            }
                        }
                    }
                }
            });

            updateHorizontalChart(chart, dataset, metricKey, paletteKey);
            return chart;
        };

        const updateMonthlyTrendChart = (chart, dataset, metricKey) => {
            if (!chart || !trendMetricSettings[metricKey]) {
                return;
            }

            const config = trendMetricSettings[metricKey];
            const positiveData = dataset.map(item => Number(item[config.positiveKey]) || 0);
            const negativeData = dataset.map(item => Number(item[config.negativeKey]) || 0);

            const minValue = Math.min(...positiveData, ...negativeData);
            const maxValue = Math.max(...positiveData, ...negativeData);
            const padding = (Math.abs(maxValue - minValue) || 1) * 0.15;

            chart.data.datasets[0].label = config.positiveLabel;
            chart.data.datasets[0].data = positiveData;
            chart.data.datasets[0].borderColor = config.colors.positive;
            chart.data.datasets[0].pointBorderColor = config.colors.positive;
            chart.data.datasets[0].backgroundColor = hexToRGBA(config.colors.positive, 0.18);

            chart.data.datasets[1].label = config.negativeLabel;
            chart.data.datasets[1].data = negativeData;
            chart.data.datasets[1].borderColor = config.colors.negative;
            chart.data.datasets[1].pointBorderColor = config.colors.negative;
            chart.data.datasets[1].backgroundColor = hexToRGBA(config.colors.negative, 0.18);

            chart.options.scales.y.ticks.callback = value => config.formatter(value);
            chart.options.plugins.tooltip.callbacks.label = context =>
                `${context.dataset.label}: ${config.formatter(context.parsed.y)}`;

            chart.options.scales.y.min = minValue - padding;
            chart.options.scales.y.max = maxValue + padding;

            chart.update();
        };

        const buildMonthlyTrendChart = (canvasId, dataset, metricKey) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !dataset.length || !trendMetricSettings[metricKey]) {
                return null;
            }

            const config = trendMetricSettings[metricKey];
            const ctx = canvas.getContext('2d');
            const labels = dataset.map(item => {
                const [year, month] = item.month.split('-').map(Number);
                return monthFormatter.format(new Date(year, month - 1));
            });

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: config.positiveLabel,
                        data: dataset.map(item => Number(item[config.positiveKey]) || 0),
                        borderColor: config.colors.positive,
                        backgroundColor: hexToRGBA(config.colors.positive, 0.18),
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: config.colors.positive,
                        tension: 0.35,
                        fill: true
                    }, {
                        label: config.negativeLabel,
                        data: dataset.map(item => Number(item[config.negativeKey]) || 0),
                        borderColor: config.colors.negative,
                        backgroundColor: hexToRGBA(config.colors.negative, 0.18),
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: config.colors.negative,
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: colors.text
                            }
                        },
                        y: {
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                color: colors.text,
                                callback: value => config.formatter(value)
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                color: colors.text
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${context.dataset.label}: ${config.formatter(context.parsed.y)}`
                            }
                        }
                    }
                }
            });

            updateMonthlyTrendChart(chart, dataset, metricKey);
            return chart;
        };

        const getFallbackDoctorName = idDoctor => (idDoctor ? `Doctor ${idDoctor}` : 'Sin identificar');

        const ensureDoctorNames = processedData => {
            if (!processedData) {
                return;
            }

            const collections = [
                processedData.doctorComparisons || [],
                processedData.positiveByAmount || [],
                processedData.negativeByAmount || [],
                processedData.positiveByQuantity || [],
                processedData.negativeByQuantity || []
            ];

            const applyResolvedName = doctor => {
                if (!doctor || !doctor.id_doctor) {
                    return;
                }

                const preferredName = doctor.name || doctor.doctor || doctor.doctor_name ||
                    doctorNameCache.get(doctor.id_doctor) || getFallbackDoctorName(doctor.id_doctor);

                doctorNameCache.set(doctor.id_doctor, preferredName);
                doctor.name = preferredName;
                doctor.doctor = preferredName;
                doctor.doctor_name = preferredName;
            };

            collections.forEach(group => {
                group.forEach(applyResolvedName);
            });
        };

        const getMetricKeyForCurrent = metric => metricSettings[metric] ? metricSettings[metric].key : 'amount_diff';

        const sortDoctorComparisons = (doctors = [], ordering = 'negatives', metric = 'amount') => {
            const key = getMetricKeyForCurrent(metric);
            return [...doctors].sort((a, b) => {
                const valA = Number(a[key] || 0);
                const valB = Number(b[key] || 0);

                if (ordering === 'negatives') {
                    return valA - valB;
                }

                return valB - valA;
            });
        };

        const updateStatistics = stats => {
            const negativeAmountEl = document.getElementById('seguimientoAvgNegativeAmount');
            const negativeQuantityEl = document.getElementById('seguimientoAvgNegativeQuantity');
            const positiveAmountEl = document.getElementById('seguimientoAvgPositiveAmount');
            const positiveQuantityEl = document.getElementById('seguimientoAvgPositiveQuantity');
            const totalDoctorsEl = document.getElementById('seguimientoTotalDoctors');
            const trendSummaryEl = document.getElementById('seguimientoTrendSummary');

            if (negativeAmountEl) {
                negativeAmountEl.textContent = formatSignedCurrency(stats.avgNegativeAmount);
                negativeAmountEl.classList.toggle('text-success', stats.avgNegativeAmount > 0);
                negativeAmountEl.classList.toggle('text-danger', stats.avgNegativeAmount < 0);
            }

            if (negativeQuantityEl) {
                negativeQuantityEl.textContent = `${formatSignedQuantity(stats.avgNegativeQuantity)} pedidos`;
            }

            if (positiveAmountEl) {
                positiveAmountEl.textContent = formatSignedCurrency(stats.avgPositiveAmount);
                positiveAmountEl.classList.toggle('text-success', stats.avgPositiveAmount > 0);
                positiveAmountEl.classList.toggle('text-danger', stats.avgPositiveAmount < 0);
            }

            if (positiveQuantityEl) {
                positiveQuantityEl.textContent = `${formatSignedQuantity(stats.avgPositiveQuantity)} pedidos`;
            }

            if (totalDoctorsEl) {
                totalDoctorsEl.textContent = quantityFormatter.format(stats.totalDoctors || 0);
            }

            if (trendSummaryEl) {
                const amountLabel = `${currencyFormatter.format(stats.firstAverageAmount)} → ${currencyFormatter.format(stats.secondAverageAmount)}`;
                const quantityLabel = `${quantityFormatter.format(stats.firstAverageQuantity)} → ${quantityFormatter.format(stats.secondAverageQuantity)}`;
                trendSummaryEl.textContent = `Promedio monto: ${amountLabel} | Promedio pedidos: ${quantityLabel}`;
            }
        };

        const updateDoctorsTable = doctors => {
            const tbody = document.getElementById('seguimientoDoctorTableBody');
            const summary = document.getElementById('seguimientoTableSummary');

            if (!tbody) {
                return;
            }

            if (!doctors.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay información disponible para los filtros seleccionados.</td></tr>';
                if (summary) {
                    summary.textContent = 'Sin datos';
                }
                return;
            }

            tbody.innerHTML = doctors.map((doctor, index) => {
                const amountDiff = Number(doctor.amount_diff) || 0;
                const quantityDiff = Number(doctor.quantity_diff) || 0;
                const amountTrendClass = amountDiff >= 0 ? 'badge-grobdi badge-green' : 'badge-grobdi badge-red';
                const quantityTrendClass = quantityDiff >= 0 ? 'badge-grobdi badge-green' : 'badge-grobdi badge-red';

                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="text-left">${doctor.name}</td>
                        <td data-metric="quantity">
                            <span class="badge-grobdi badge-gray">${quantityFormatter.format(doctor.quantity_filter_1)} pedidos</span>
                        </td>
                        <td data-metric="quantity">
                            <span class="${quantityTrendClass}">${quantityFormatter.format(doctor.quantity_filter_2)} pedidos</span>
                        </td>
                        <td data-metric="quantity">
                            <span class="${quantityTrendClass}">${formatSignedQuantity(quantityDiff)} pedidos</span>
                        </td>
                        <td data-metric="amount">
                            <span class="badge-grobdi badge-gray">${currencyFormatter.format(doctor.amount_filter_1)}</span>
                        </td>
                        <td data-metric="amount">
                            <span class="${amountTrendClass}">${currencyFormatter.format(doctor.amount_filter_2)}</span>
                        </td>
                        <td data-metric="amount">
                            <span class="${amountTrendClass}">${formatSignedCurrency(amountDiff)}</span>
                        </td>
                    </tr>
                `;
            }).join('');

            if (summary) {
                const positives = doctors.filter(doc => Number(doc.amount_diff || 0) > 0).length;
                const negatives = doctors.filter(doc => Number(doc.amount_diff || 0) < 0).length;
                summary.textContent = `Mostrando ${doctors.length} doctores (${positives} positivos / ${negatives} negativos)`;
            }

            applyMetricToTables(currentMetric);
        };

        const syncFormWithFilters = filters => {
            const toMonthValue = value => {
                const parsed = parseDateValue(value);
                if (!parsed) {
                    return '';
                }
                return `${parsed.getFullYear()}-${String(parsed.getMonth() + 1).padStart(2, '0')}`;
            };

            const setValue = (input, value) => {
                if (!input) {
                    return;
                }

                if (!input.dataset.initialDefault) {
                    input.dataset.initialDefault = input.dataset.default || input.value || '';
                }

                const monthValue = toMonthValue(value);
                if (monthValue) {
                    input.value = monthValue;
                }
            };

            setValue(filterForm.range_a_start, filters?.start_date_1);
            setValue(filterForm.range_a_end, filters?.end_date_1);
            setValue(filterForm.range_b_start, filters?.start_date_2);
            setValue(filterForm.range_b_end, filters?.end_date_2);
        };

        const doctorEntryDefaults = entry => {
            const idDoctor = entry.id_doctor ?? entry.id ?? null;
            const name = entry.doctor_name || entry.doctor || entry.name || getFallbackDoctorName(idDoctor);

            const prevAmount = Number(entry.prev_amount ?? entry.amount_filter_1 ?? 0);
            const currAmount = Number(entry.curr_amount ?? entry.amount_filter_2 ?? 0);
            const prevQuantity = Number(entry.prev_quantity ?? entry.quantity_filter_1 ?? 0);
            const currQuantity = Number(entry.curr_quantity ?? entry.quantity_filter_2 ?? 0);

            const amountDiff = Number(entry.amount_fluctuation ?? entry.amount_diff ?? (currAmount - prevAmount));
            const quantityDiff = Number(entry.quantity_fluctuation ?? entry.quantity_diff ?? (currQuantity - prevQuantity));

            return {
                id_doctor: idDoctor,
                name,
                doctor: name,
                doctor_name: name,
                amount_filter_1: prevAmount,
                amount_filter_2: currAmount,
                amount_diff: amountDiff,
                quantity_filter_1: prevQuantity,
                quantity_filter_2: currQuantity,
                quantity_diff: quantityDiff
            };
        };

        const processBackendData = payload => {
            if (!payload) {
                return null;
            }

            const {
                top_stats: topStats = {},
                general_Stats: generalStats = {},
                data: rawComparisons = [],
                filters: rawFilters = {}
            } = payload;

            const toArray = value => Array.isArray(value) ? value : Object.values(value || {});

            let positiveByAmount = toArray(topStats.amount_increase).map(doctorEntryDefaults);
            let negativeByAmount = toArray(topStats.amount_decrease).map(doctorEntryDefaults);
            let positiveByQuantity = toArray(topStats.quantity_increase).map(doctorEntryDefaults);
            let negativeByQuantity = toArray(topStats.quantity_decrease).map(doctorEntryDefaults);
            const doctorComparisons = toArray(rawComparisons).map(doctorEntryDefaults);

            if (!doctorComparisons.length) {
                const unique = new Map();
                [...positiveByAmount, ...negativeByAmount, ...positiveByQuantity, ...negativeByQuantity]
                    .forEach(doctor => {
                        const key = doctor.id_doctor ?? doctor.name;
                        if (!unique.has(key)) {
                            unique.set(key, doctor);
                        }
                    });

                doctorComparisons.push(...unique.values());
            }

            const buildSeriesFromComparisons = (collection, key, direction = 'positive') => collection
                .filter(item => {
                    const value = Number(item[key] || 0);
                    return direction === 'positive' ? value > 0 : value < 0;
                })
                .sort((a, b) => {
                    const valueA = Number(a[key] || 0);
                    const valueB = Number(b[key] || 0);
                    return direction === 'positive' ? valueB - valueA : valueA - valueB;
                })
                .slice(0, 10);

            const fallbackPositiveAmount = buildSeriesFromComparisons(doctorComparisons, 'amount_diff', 'positive');
            const fallbackNegativeAmount = buildSeriesFromComparisons(doctorComparisons, 'amount_diff', 'negative');
            const fallbackPositiveQuantity = buildSeriesFromComparisons(doctorComparisons, 'quantity_diff', 'positive');
            const fallbackNegativeQuantity = buildSeriesFromComparisons(doctorComparisons, 'quantity_diff', 'negative');

            if (!positiveByAmount.length) {
                positiveByAmount = fallbackPositiveAmount;
            }
            if (!negativeByAmount.length) {
                negativeByAmount = fallbackNegativeAmount;
            }
            if (!positiveByQuantity.length) {
                positiveByQuantity = fallbackPositiveQuantity;
            }
            if (!negativeByQuantity.length) {
                negativeByQuantity = fallbackNegativeQuantity;
            }

            let positiveAmountDocs = doctorComparisons.filter(doc => doc.amount_diff > 0);
            let negativeAmountDocs = doctorComparisons.filter(doc => doc.amount_diff < 0);
            let positiveQuantityDocs = doctorComparisons.filter(doc => doc.quantity_diff > 0);
            let negativeQuantityDocs = doctorComparisons.filter(doc => doc.quantity_diff < 0);

            if (!positiveAmountDocs.length) {
                positiveAmountDocs = [...fallbackPositiveAmount];
            }
            if (!negativeAmountDocs.length) {
                negativeAmountDocs = [...fallbackNegativeAmount];
            }
            if (!positiveQuantityDocs.length) {
                positiveQuantityDocs = [...fallbackPositiveQuantity];
            }
            if (!negativeQuantityDocs.length) {
                negativeQuantityDocs = [...fallbackNegativeQuantity];
            }

            const computeAverage = (collection, key) => {
                if (!collection.length) {
                    return 0;
                }

                const total = collection.reduce((sum, item) => sum + Number(item[key] || 0), 0);
                return total / collection.length;
            };

            const averages = generalStats.averages || {};
            const firstAverage = averages.first || {};
            const secondAverage = averages.second || {};

            const stats = {
                avgPositiveAmount: computeAverage(positiveAmountDocs, 'amount_diff'),
                avgNegativeAmount: computeAverage(negativeAmountDocs, 'amount_diff'),
                avgPositiveQuantity: computeAverage(positiveQuantityDocs, 'quantity_diff'),
                avgNegativeQuantity: computeAverage(negativeQuantityDocs, 'quantity_diff'),
                totalDoctors: Number(generalStats.total_doctores ?? doctorComparisons.length ?? 0),
                totalPositiveAmountDoctors: positiveAmountDocs.length,
                totalNegativeAmountDoctors: negativeAmountDocs.length,
                totalPositiveQuantityDoctors: positiveQuantityDocs.length,
                totalNegativeQuantityDoctors: negativeQuantityDocs.length,
                firstAverageAmount: Number(firstAverage.amount ?? 0),
                secondAverageAmount: Number(secondAverage.amount ?? 0),
                firstAverageQuantity: Number(firstAverage.quantity ?? 0),
                secondAverageQuantity: Number(secondAverage.quantity ?? 0)
            };

            const rangeLabels = {
                first: buildRangeLabel(rawFilters.start_date_1, rawFilters.end_date_1),
                second: buildRangeLabel(rawFilters.start_date_2, rawFilters.end_date_2)
            };

            const combinedLabel = rangeLabels.first && rangeLabels.second
                ? `${rangeLabels.first} vs ${rangeLabels.second}`
                : rangeLabels.first || rangeLabels.second || '';

            const comparisonsForTrend = doctorComparisons.length
                ? doctorComparisons
                : [...positiveByAmount, ...negativeByAmount, ...positiveByQuantity, ...negativeByQuantity];

            const generateMonthlyTrend = (filters = {}, doctors = []) => {
                const start = parseDateValue(filters.start_date_1) || parseDateValue(filters.start_date_2);
                const end = parseDateValue(filters.end_date_2) || parseDateValue(filters.end_date_1);

                if (!start || !end || start > end) {
                    return [];
                }

                const months = [];
                const cursor = new Date(start.getFullYear(), start.getMonth(), 1);
                const limit = new Date(end.getFullYear(), end.getMonth(), 1);

                while (cursor <= limit) {
                    months.push(`${cursor.getFullYear()}-${String(cursor.getMonth() + 1).padStart(2, '0')}`);
                    cursor.setMonth(cursor.getMonth() + 1);
                }

                if (!months.length) {
                    months.push(`${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}`);
                }

                const totals = doctors.reduce((acc, doctor) => {
                    const amountDiff = Number(doctor.amount_diff) || 0;
                    const quantityDiff = Number(doctor.quantity_diff) || 0;

                    if (amountDiff >= 0) {
                        acc.positiveAmount += amountDiff;
                    } else {
                        acc.negativeAmount += Math.abs(amountDiff);
                    }

                    if (quantityDiff >= 0) {
                        acc.positiveQuantity += quantityDiff;
                    } else {
                        acc.negativeQuantity += Math.abs(quantityDiff);
                    }

                    return acc;
                }, {
                    positiveAmount: 0,
                    negativeAmount: 0,
                    positiveQuantity: 0,
                    negativeQuantity: 0
                });

                return months.map((month, index) => {
                    const progress = (index + 1) / months.length;

                    return {
                        month,
                        positive_amount: Math.round(totals.positiveAmount * progress),
                        negative_amount: -Math.round(totals.negativeAmount * progress),
                        positive_quantity: Math.round(totals.positiveQuantity * progress),
                        negative_quantity: -Math.round(totals.negativeQuantity * progress)
                    };
                });
            };

            const monthlyPerformance = generateMonthlyTrend(rawFilters, comparisonsForTrend);

            return {
                positiveByAmount,
                negativeByAmount,
                positiveByQuantity,
                negativeByQuantity,
                doctorComparisons,
                monthlyPerformance,
                stats,
                filters: {
                    ...rawFilters,
                    rangeLabels,
                    combinedLabel
                }
            };
        };

        let currentMetric = 'amount';
        let currentOrdering = 'negatives';

        const appState = {
            filters: {},
            initialFilters: null,
            initialRequestFilters: null,
            lastRequestFilters: {},
            data: null,
            charts: {
                positive: null,
                negative: null,
                trend: null
            }
        };

        const setOrderButtonState = ordering => {
            if (!filterFormElements.orderButton) {
                return;
            }

            filterFormElements.orderButton.textContent = ordering === 'negatives' ? 'Negativos' : 'Positivos';
            filterFormElements.orderButton.dataset.ordering = ordering;
        };

        if (filterFormElements.orderButton) {
            filterFormElements.orderButton.addEventListener('click', () => {
                currentOrdering = currentOrdering === 'negatives' ? 'positives' : 'negatives';
                setOrderButtonState(currentOrdering);

                if (!appState.data) {
                    return;
                }

                const sorted = sortDoctorComparisons(appState.data.doctorComparisons || [], currentOrdering, currentMetric);
                updateDoctorsTable(sorted);
            });
        }

        const fetchReportData = async (filters = {}) => {
            try {
                const params = new URLSearchParams();

                Object.entries(filters).forEach(([key, value]) => {
                    if (value) {
                        params.append(key, value);
                    }
                });

                const queryString = params.toString();
                const endpoint = queryString ? `${endpointBase}?${queryString}` : endpointBase;

                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return await response.json();
            } catch (error) {
                console.error('Error al cargar los datos del reporte:', error);
                return null;
            }
        };

        const renderReport = async (filters = {}) => {
            const requestFilters = extractRequestFilters(filters);
            const backendData = await fetchReportData(requestFilters);
            if (!backendData) {
                console.error('No se pudieron cargar los datos del reporte');
                return;
            }

            const processedData = processBackendData(backendData);
            if (!processedData) {
                console.error('Error al procesar los datos del backend');
                return;
            }

            ensureDoctorNames(processedData);

            appState.data = processedData;
            appState.filters = processedData.filters || {};
            appState.lastRequestFilters = { ...requestFilters };

            if (!appState.initialFilters) {
                appState.initialFilters = { ...appState.filters };
                appState.initialRequestFilters = { ...requestFilters };
            }

            syncFormWithFilters(appState.filters);

            if (filterFormElements.rangeLabel) {
                filterFormElements.rangeLabel.textContent = appState.filters.combinedLabel || defaultLabel;
            }

            updateStatistics(processedData.stats);

            const sortedComparisons = sortDoctorComparisons(processedData.doctorComparisons || [], currentOrdering, currentMetric);
            updateDoctorsTable(sortedComparisons);

            const datasets = currentMetric === 'amount'
                ? {
                    positive: processedData.positiveByAmount,
                    negative: processedData.negativeByAmount
                }
                : {
                    positive: processedData.positiveByQuantity,
                    negative: processedData.negativeByQuantity
                };

            if (appState.charts.positive) {
                updateHorizontalChart(appState.charts.positive, datasets.positive, currentMetric, 'positives');
            } else {
                appState.charts.positive = buildHorizontalChart('chartSeguimientoMax', datasets.positive, currentMetric, 'positives');
            }

            if (appState.charts.negative) {
                updateHorizontalChart(appState.charts.negative, datasets.negative, currentMetric, 'negatives');
            } else {
                appState.charts.negative = buildHorizontalChart('chartSeguimientoMin', datasets.negative, currentMetric, 'negatives');
            }

            if (appState.charts.trend) {
                updateMonthlyTrendChart(appState.charts.trend, processedData.monthlyPerformance, currentMetric);
            } else {
                appState.charts.trend = buildMonthlyTrendChart('chartSeguimientoTrend', processedData.monthlyPerformance, currentMetric);
            }
        };

        const handleMetricChange = metric => {
            if (!metricSettings[metric]) {
                return;
            }

            currentMetric = metric;
            setActiveMetricButton(metric);
            updateMetricStatus(metric);
            applyMetricToTables(metric);

            if (!appState.data) {
                return;
            }

            const datasets = currentMetric === 'amount'
                ? {
                    positive: appState.data.positiveByAmount,
                    negative: appState.data.negativeByAmount
                }
                : {
                    positive: appState.data.positiveByQuantity,
                    negative: appState.data.negativeByQuantity
                };

            updateHorizontalChart(appState.charts.positive, datasets.positive, metric, 'positives');
            updateHorizontalChart(appState.charts.negative, datasets.negative, metric, 'negatives');
            updateMonthlyTrendChart(appState.charts.trend, appState.data.monthlyPerformance, metric);

            const sortedComparisons = sortDoctorComparisons(appState.data.doctorComparisons || [], currentOrdering, metric);
            updateDoctorsTable(sortedComparisons);
        };

        filterFormElements.metricButtons.forEach(button => {
            button.addEventListener('click', () => {
                const metric = button.dataset.seguimientoMetric;
                if (metric && metric !== currentMetric) {
                    handleMetricChange(metric);
                }
            });
        });

        applyMetricToTables(currentMetric);
        setActiveMetricButton(currentMetric);
        updateMetricStatus(currentMetric);

        filterForm.addEventListener('submit', event => {
            event.preventDefault();

            const rangeAStart = filterForm.range_a_start?.value || '';
            const rangeAEnd = filterForm.range_a_end?.value || '';
            const rangeBStart = filterForm.range_b_start?.value || '';
            const rangeBEnd = filterForm.range_b_end?.value || '';

            const rangeA = formatRange(rangeAStart, rangeAEnd);
            const rangeB = formatRange(rangeBStart, rangeBEnd);

            if (filterFormElements.rangeLabel) {
                filterFormElements.rangeLabel.textContent = rangeA && rangeB ? `${rangeA} vs ${rangeB}` : defaultLabel;
            }

            const filters = {
                start_date_1: getFirstDayOfMonth(rangeAStart),
                end_date_1: getLastDayOfMonth(rangeAEnd),
                start_date_2: getFirstDayOfMonth(rangeBStart),
                end_date_2: getLastDayOfMonth(rangeBEnd)
            };

            renderReport(filters);
        });

        filterFormElements.resetButton?.addEventListener('click', () => {
            const baselineFilters = appState.initialFilters || {};
            const baselineRequest = appState.initialRequestFilters || {};

            if (Object.keys(baselineFilters).length) {
                syncFormWithFilters(baselineFilters);
            } else {
                ['range_a_start', 'range_a_end', 'range_b_start', 'range_b_end'].forEach(key => {
                    const input = filterForm[key];
                    if (input) {
                        const initial = input.dataset.initialDefault || input.dataset.default || '';
                        input.value = initial;
                    }
                });
            }

            if (filterFormElements.rangeLabel) {
                filterFormElements.rangeLabel.textContent = baselineFilters.combinedLabel || defaultLabel;
            }

            handleMetricChange('amount');
            currentOrdering = 'negatives';
            setOrderButtonState(currentOrdering);
            renderReport(baselineRequest);
        });

        applyMetricToTables(currentMetric);
        setActiveMetricButton(currentMetric);
        updateMetricStatus(currentMetric);
        setOrderButtonState(currentOrdering);
        renderReport();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSeguimientoModule);
    } else {
        initSeguimientoModule();
    }
})();
