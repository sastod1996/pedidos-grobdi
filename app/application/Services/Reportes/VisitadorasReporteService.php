<?php

namespace App\Application\Services\Reportes;

use App\Application\DTOs\Reportes\VisitadorasDTO;
use App\Application\DTOs\Reportes\ReporteDTO;

/**
 * Servicio específico para reportes de visitadoras
 *
 * Este servicio maneja toda la lógica de negocio relacionada con reportes de visitadoras:
 * - Creación del DTO de visitadoras
 * - Definición de filtros válidos para visitadoras
 * - Aplicación de filtros específicos (por mes, año, zona, distrito)
 * - Validación de datos
 *
 * Extiende BaseReporteService para heredar funcionalidad común como caching.
 */
class VisitadorasReporteService extends BaseReporteService
{
    // Prefijo específico para cache de visitadoras
    protected string $cachePrefix = 'visitadoras_reporte_';

    /**
     * Crea el DTO específico para datos de visitadoras
     *
     * @param array $filtros Filtros aplicados
     * @return ReporteData DTO con datos de visitadoras
     */
    // protected function createReporteData(array $filtros = []): ReporteData

    /**
     * Define los filtros válidos para reportes de visitadoras
     *
     * @return array Configuración de filtros permitidos
     */
    // protected function getFiltrosValidos(): array

    /**
     * Aplica filtros específicos para reportes de visitadoras
     *
     * @param ReporteData $data Datos del reporte
     * @param array $filtros Filtros a aplicar
     * @return ReporteData Datos filtrados
     */
    // public function aplicarFiltros(ReporteData $data, array $filtros): ReporteData

    /**
     * Aplica filtro por mes específico
     */
    // private function filtrarPorMes(VisitadorasData $data, string $mes): VisitadorasData

    /**
     * Aplica filtro por año específico
     */
    // private function filtrarPorAnio(VisitadorasData $data, int $anio): VisitadorasData

    /**
     * Aplica filtro por zona específica
     */
    // private function filtrarPorZona(VisitadorasData $data, string $zona): VisitadorasData

    /**
     * Aplica filtro por distrito específico
     */
    // private function filtrarPorDistrito(VisitadorasData $data, string $distrito): VisitadorasData
}