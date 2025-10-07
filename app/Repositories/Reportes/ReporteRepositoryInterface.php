<?php

namespace App\Repositories\Reportes;

use Illuminate\Database\Eloquent\Collection;

interface ReporteRepositoryInterface
{
    public function getDatosBasicos(): array;
    public function getDatosFiltrados(array $filtros = []): Collection;
    public function getEstadisticas(array $filtros = []): array;
    public function getFiltrosDisponibles(): array;
}