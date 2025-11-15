(() => {
    const init = () => {
        const modals = document.querySelectorAll('[data-recipes-modal]');
        if (!modals.length) {
            return;
        }

        const formatSize = (bytes) => {
            if (!bytes) {
                return '0 B';
            }
            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const size = bytes / Math.pow(1024, exponent);
            return `${size.toFixed(size >= 10 ? 0 : 1)} ${units[exponent]}`;
        };

        modals.forEach((modal) => {
            const modalId = modal.dataset.modalId || modal.id;
            const storeInput = modal.querySelector('[data-recipes-input]');
            const pickerInput = modal.querySelector('[data-recipes-picker]');
            const dropzone = modal.querySelector('[data-recipes-dropzone]');
            const previewList = modal.querySelector('[data-recipes-preview]');
            const emptyState = modal.querySelector('[data-recipes-empty]');
            const feedback = modal.querySelector('[data-recipes-feedback]');
            const acceptBtn = modal.querySelector('[data-recipes-accept]');
            const miniContainer = document.querySelector(`[data-recipes-mini="${modalId}"]`);
            const counter = document.querySelector(`[data-recipes-count="${modalId}"]`);

            const maxFiles = Number(modal.dataset.recipesMaxFiles) || 12;
            const maxSizeBytes = (Number(modal.dataset.recipesMaxSize) || 8) * 1024 * 1024;

            const state = {
                working: [],
                committed: storeInput?.files?.length ? Array.from(storeInput.files) : []
            };

            const setFeedback = (message = '', tone = 'muted') => {
                if (!feedback) {
                    return;
                }
                feedback.textContent = message;
                feedback.className = tone ? `text-${tone} small` : 'text-muted small';
            };

            const renderPreview = () => {
                if (!previewList) {
                    return;
                }

                previewList.innerHTML = '';
                if (!state.working.length) {
                    emptyState?.classList.remove('d-none');
                    previewList.classList.add('d-none');
                    return;
                }

                emptyState?.classList.add('d-none');
                previewList.classList.remove('d-none');

                state.working.forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'recipes-preview-item';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'recipes-preview-item__remove';
                    removeBtn.dataset.removeIndex = String(index);
                    removeBtn.innerHTML = '&times;';

                    const img = document.createElement('img');
                    img.className = 'recipes-preview-item__thumb';
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    img.onload = () => URL.revokeObjectURL(img.src);

                    const meta = document.createElement('div');
                    meta.className = 'recipes-preview-item__meta';

                    const name = document.createElement('span');
                    name.className = 'recipes-preview-item__name';
                    name.textContent = file.name;

                    const size = document.createElement('span');
                    size.className = 'recipes-preview-item__size';
                    size.textContent = formatSize(file.size);

                    meta.appendChild(name);
                    meta.appendChild(size);

                    item.appendChild(removeBtn);
                    item.appendChild(img);
                    item.appendChild(meta);
                    previewList.appendChild(item);
                });
            };

            const renderMini = () => {
                if (!miniContainer) {
                    return;
                }

                miniContainer.innerHTML = '';
                if (!state.committed.length) {
                    const empty = document.createElement('div');
                    empty.className = 'recipe-mini-carousel__empty';
                    empty.textContent = 'Agrega recetas para visualizarlas aquí.';
                    miniContainer.appendChild(empty);
                    return;
                }

                const track = document.createElement('div');
                track.className = 'recipe-mini-carousel__track';
                state.committed.forEach((file) => {
                    const thumbWrapper = document.createElement('div');
                    thumbWrapper.className = 'recipe-mini-thumb';
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    img.onload = () => URL.revokeObjectURL(img.src);
                    thumbWrapper.appendChild(img);
                    track.appendChild(thumbWrapper);
                });
                miniContainer.appendChild(track);
            };

            const updateCounter = () => {
                if (counter) {
                    counter.textContent = String(state.committed.length);
                }
            };

            const syncStoreInput = () => {
                if (!storeInput) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                state.committed.forEach((file) => dataTransfer.items.add(file));
                storeInput.files = dataTransfer.files;
            };

            const commitWorking = () => {
                state.committed = [...state.working];
                syncStoreInput();
                renderMini();
                updateCounter();
            };

            const addFiles = (fileList) => {
                if (!fileList?.length) {
                    return;
                }

                let added = 0;
                for (const file of Array.from(fileList)) {
                    if (!file.type.startsWith('image/')) {
                        setFeedback('Solo se permiten imágenes.', 'danger');
                        continue;
                    }
                    if (file.size > maxSizeBytes) {
                        setFeedback(`"${file.name}" supera el tamaño máximo permitido.`, 'danger');
                        continue;
                    }
                    if (state.working.length >= maxFiles) {
                        setFeedback(`Límite de ${maxFiles} recetas alcanzado.`, 'warning');
                        break;
                    }
                    state.working.push(file);
                    added += 1;
                }

                if (added) {
                    setFeedback(`${added} receta(s) lista(s) para adjuntar.`, 'success');
                    renderPreview();
                }
            };

            const removeFileAt = (index) => {
                if (index < 0 || index >= state.working.length) {
                    return;
                }
                state.working.splice(index, 1);
                renderPreview();
                setFeedback('Receta eliminada.', 'warning');
            };

            previewList?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-index]');
                if (!button) {
                    return;
                }
                const index = Number(button.dataset.removeIndex);
                removeFileAt(index);
            });

            dropzone?.addEventListener('click', () => pickerInput?.click());
            dropzone?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    pickerInput?.click();
                }
            });

            dropzone?.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropzone.classList.add('recipe-dropzone--hover');
            });

            dropzone?.addEventListener('dragleave', () => {
                dropzone.classList.remove('recipe-dropzone--hover');
            });

            dropzone?.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('recipe-dropzone--hover');
                addFiles(event.dataTransfer?.files || []);
            });

            pickerInput?.addEventListener('change', (event) => {
                addFiles(event.target.files || []);
                event.target.value = '';
            });

            acceptBtn?.addEventListener('click', () => {
                commitWorking();
                setFeedback('Recetas guardadas correctamente.', 'success');
                if (
                    typeof window.jQuery !== 'undefined' &&
                    window.jQuery.fn &&
                    window.jQuery.fn.modal
                ) {
                    window.jQuery(modal).modal('hide');
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                    instance.hide();
                }
            });

            const handleShow = () => {
                state.working = [...state.committed];
                renderPreview();
                if (!state.working.length) {
                    emptyState?.classList.remove('d-none');
                }
                setFeedback('', 'muted');
            };

            modal.addEventListener('show.bs.modal', handleShow);

            renderMini();
            updateCounter();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
