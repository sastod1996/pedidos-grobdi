(() => {
    const containers = document.querySelectorAll('[data-location-selector]');
    if (!containers.length) {
        return;
    }

    const fetchJson = async (url) => {
        if (!url) {
            return null;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('No se pudo cargar la ubicación:', error);
            return null;
        }
    };

    const populateSelect = (select, data, placeholder) => {
        if (!select) {
            return;
        }

        select.innerHTML = '';
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder || 'Selecciona una opción';
        select.appendChild(placeholderOption);

        (data || []).forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            select.appendChild(option);
        });

        select.disabled = select.options.length <= 1;
    };

    const setLoading = (select, isLoading) => {
        if (!select) {
            return;
        }

        if (isLoading) {
            select.disabled = true;
            select.dataset.loading = '1';
            const loadingOption = document.createElement('option');
            loadingOption.value = '';
            loadingOption.textContent = 'Cargando...';
            select.innerHTML = '';
            select.appendChild(loadingOption);
        } else if (select.dataset.loading) {
            delete select.dataset.loading;
        }
    };

    containers.forEach((container) => {
        const departamentoSelect = container.querySelector('[data-role="departamento"]');
        const provinciaSelect = container.querySelector('[data-role="provincia"]');
        const distritoSelect = container.querySelector('[data-role="distrito"]');
        const hiddenDepartamento = container.querySelector('[data-role="hidden-departamento"]');
        const hiddenProvincia = container.querySelector('[data-role="hidden-provincia"]');

        const endpoints = {
            departamentos: container.dataset.departamentosEndpoint || '',
            provincias: container.dataset.provinciasEndpoint || '',
            distritos: container.dataset.distritosEndpoint || '',
            chain: container.dataset.chainEndpoint || ''
        };

        const state = {
            departamento: container.dataset.initialDepartamento || '',
            provincia: container.dataset.initialProvincia || '',
            distrito: container.dataset.initialDistrito || distritoSelect?.value || ''
        };

        const updateHiddenInputs = () => {
            if (hiddenDepartamento) {
                hiddenDepartamento.value = departamentoSelect?.value || '';
            }
            if (hiddenProvincia) {
                hiddenProvincia.value = provinciaSelect?.value || '';
            }
        };

        const loadDepartamentos = async () => {
            if (!departamentoSelect || !endpoints.departamentos) {
                return;
            }

            setLoading(departamentoSelect, true);
            const payload = await fetchJson(endpoints.departamentos);
            const data = payload?.data || [];
            populateSelect(departamentoSelect, data, departamentoSelect.dataset.placeholder);
            setLoading(departamentoSelect, false);

            if (state.departamento) {
                departamentoSelect.value = state.departamento;
            }
        };

        const loadProvincias = async (departamentoId, preselect = '') => {
            if (!provinciaSelect || !endpoints.provincias || !departamentoId) {
                populateSelect(provinciaSelect, [], provinciaSelect?.dataset.placeholder);
                populateSelect(distritoSelect, [], distritoSelect?.dataset.placeholder);
                return;
            }

            setLoading(provinciaSelect, true);
            const url = endpoints.provincias.replace('__departamento__', departamentoId);
            const payload = await fetchJson(url);
            const data = payload?.data || [];
            populateSelect(provinciaSelect, data, provinciaSelect.dataset.placeholder);
            setLoading(provinciaSelect, false);

            provinciaSelect.value = preselect || '';
            if (preselect) {
                updateHiddenInputs();
            } else {
                provinciaSelect.dispatchEvent(new Event('change'));
            }
        };

        const loadDistritos = async (provinciaId, preselect = '') => {
            if (!distritoSelect || !endpoints.distritos || !provinciaId) {
                populateSelect(distritoSelect, [], distritoSelect?.dataset.placeholder);
                return;
            }

            setLoading(distritoSelect, true);
            const url = endpoints.distritos.replace('__provincia__', provinciaId);
            const payload = await fetchJson(url);
            const data = payload?.data || [];
            populateSelect(distritoSelect, data, distritoSelect.dataset.placeholder);
            setLoading(distritoSelect, false);

            if (preselect) {
                distritoSelect.value = preselect;
                updateHiddenInputs();
            }
        };

        const hydrateFromDistrito = async (distritoId) => {
            if (!distritoId || !endpoints.chain) {
                return;
            }

            const url = endpoints.chain.replace('__distrito__', distritoId);
            const payload = await fetchJson(url);
            if (!payload) {
                return;
            }

            state.departamento = payload.departamento?.id ? String(payload.departamento.id) : '';
            state.provincia = payload.provincia?.id ? String(payload.provincia.id) : '';
            state.distrito = payload.distrito?.id ? String(payload.distrito.id) : '';

            populateSelect(provinciaSelect, payload.provincias || [], provinciaSelect?.dataset.placeholder);
            populateSelect(distritoSelect, payload.distritos || [], distritoSelect?.dataset.placeholder);

            if (departamentoSelect && state.departamento) {
                departamentoSelect.value = state.departamento;
            }
            if (provinciaSelect && state.provincia) {
                provinciaSelect.value = state.provincia;
            }
            if (distritoSelect && state.distrito) {
                distritoSelect.value = state.distrito;
            }

            updateHiddenInputs();
        };

        const onDepartamentoChange = async () => {
            state.departamento = departamentoSelect?.value || '';
            state.provincia = '';
            state.distrito = '';

            populateSelect(provinciaSelect, [], provinciaSelect?.dataset.placeholder);
            populateSelect(distritoSelect, [], distritoSelect?.dataset.placeholder);
            updateHiddenInputs();

            if (state.departamento) {
                await loadProvincias(state.departamento);
            }
        };

        const onProvinciaChange = async () => {
            state.provincia = provinciaSelect?.value || '';
            state.distrito = '';

            populateSelect(distritoSelect, [], distritoSelect?.dataset.placeholder);
            updateHiddenInputs();

            if (state.provincia) {
                await loadDistritos(state.provincia);
            }
        };

        departamentoSelect?.addEventListener('change', onDepartamentoChange);
        provinciaSelect?.addEventListener('change', onProvinciaChange);
        distritoSelect?.addEventListener('change', () => {
            state.distrito = distritoSelect?.value || '';
            updateHiddenInputs();
        });

        const init = async () => {
            await loadDepartamentos();

            if (state.departamento) {
                await loadProvincias(state.departamento, state.provincia);
                if (state.provincia) {
                    await loadDistritos(state.provincia, state.distrito);
                }
            } else if (state.distrito) {
                await hydrateFromDistrito(state.distrito);
            } else {
                populateSelect(provinciaSelect, [], provinciaSelect?.dataset.placeholder);
                populateSelect(distritoSelect, [], distritoSelect?.dataset.placeholder);
            }

            updateHiddenInputs();
        };

        init();
    });
})();
