@props([
    'modalId' => 'recipesModal',
    'inputName' => 'recetas[]',
    'title' => 'Biblioteca de recetas',
    'acceptLabel' => 'Aceptar recetas',
    'maxFiles' => 12,
    'maxFileSizeMb' => 8,
])

<div
    class="modal fade recipes-modal"
    id="{{ $modalId }}"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    data-recipes-modal
    data-modal-id="{{ $modalId }}"
    data-recipes-max-files="{{ $maxFiles }}"
    data-recipes-max-size="{{ $maxFileSizeMb }}"
>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header align-items-center">
                <div>
                    <h5 class="modal-title mb-0">{{ $title }}</h5>
                    <small class="text-muted">Carga, revisa y organiza las imágenes antes de adjuntarlas.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input
                    type="file"
                    class="d-none"
                    id="{{ $modalId }}-input"
                    name="{{ $inputName }}"
                    accept="image/*"
                    multiple
                    data-recipes-input
                >
                <input
                    type="file"
                    class="d-none"
                    accept="image/*"
                    multiple
                    data-recipes-picker
                >

                <div class="recipe-dropzone" data-recipes-dropzone tabindex="0">
                    <div class="text-center">
                        <div class="mb-2">
                            <span class="recipe-dropzone__icon">🧾</span>
                        </div>
                        <p class="mb-1">Arrastra y suelta tus recetas aquí</p>
                        <p class="text-muted small mb-0">O haz clic para seleccionar desde tu equipo</p>
                        <p class="text-muted small mb-0">Hasta {{ $maxFiles }} imágenes · {{ $maxFileSizeMb }}MB por archivo</p>
                    </div>
                </div>

                <div class="recipes-preview-container">
                    <p class="text-muted text-center small mb-0" data-recipes-empty>Sin recetas seleccionadas todavía.</p>
                    <div class="recipes-preview-list" data-recipes-preview></div>
                </div>
            </div>
            <div class="modal-footer flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div class="text-muted small" data-recipes-feedback></div>
                <div class="recipes-modal__actions">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" data-recipes-accept>{{ $acceptLabel }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
