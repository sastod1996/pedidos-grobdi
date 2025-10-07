<?php

namespace App\Application\Services\Reportes;

use App\Application\DTOs\Reportes\VentasDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio específico para reportes de ventas
 *
 * Este servicio maneja toda la lógica de negocio relacionada con reportes de ventas:
 * - Creación del DTO de ventas
 * - Definición de filtros válidos para ventas
 * - Aplicación de filtros específicos (por año, mes, visitadora, provincia)
 * - Validación de datos
 *
 * Extiende BaseReporteService para heredar funcionalidad común como caching.
 */
class VentasReporteService extends BaseReporteService
{
    // Prefijo específico para cache de ventas
    protected string $cachePrefix = 'ventas_reporte_';

    /**
     * Obtiene los datos del reporte de ventas
     *
     * @param array $filtros Filtros aplicados
     * @return VentasDTO DTO con datos de ventas
     */
    public function getData(array $filtros = []): VentasDTO
    {
        // Crear cache key basado en filtros
        $cacheKey = $this->generateCacheKey($filtros);

        // Intentar obtener del cache primero
        if ($cached = $this->getFromCache($cacheKey)) {
            return $cached;
        }

        // Crear DTO con los datos
        $data = $this->createReporteData($filtros);

        // Aplicar filtros si existen
        if (!empty($filtros)) {
            $data = $this->aplicarFiltros($data, $filtros);
        }

        // Guardar en cache
        $this->saveToCache($cacheKey, $data);

        return $data;
    }

    public function getVisitadoraData(string $start_date, string $end_date)
    {
        $filtros = [
            'start_date' => Carbon::parse($start_date)->startOfDay(),
            'end_date'   => Carbon::parse($end_date)->endOfDay(),
        ];

        $dto = new VentasDTO($filtros);

        return $dto->getVentasByVisitadoraData($filtros);
    }

    /**
     * Crea el DTO específico para datos de ventas
     *
     * @param array $filtros Filtros aplicados
     * @return VentasDTO DTO con datos de ventas
     */
    protected function createReporteData(array $filtros = []): VentasDTO
    {
        return new VentasDTO($filtros);
    }

    /**
     * Define los filtros válidos para reportes de ventas
     *
     * @return array Configuración de filtros permitidos
     */
    protected function getFiltrosValidos(): array
    {
        return [
            'anio_general' => [
                'type' => 'integer',
                'values' => $this->getAniosDisponibles()
            ],
            'mes_general' => [
                'type' => 'integer',
                'range' => ['min' => 1, 'max' => 12]
            ],
            'visitadora_id' => [
                'type' => 'integer',
                'range' => ['min' => 1]
            ],
            'provincia_id' => [
                'type' => 'integer',
                'range' => ['min' => 1]
            ],
            'fecha_inicio_producto' => [
                'type' => 'date'
            ],
            'fecha_fin_producto' => [
                'type' => 'date'
            ]
        ];
    }

    /**
     * Aplica filtros específicos para reportes de ventas
     *
     * @param VentasDTO $data Datos del reporte
     * @param array $filtros Filtros a aplicar
     * @return VentasDTO Datos filtrados
     */
    public function aplicarFiltros(VentasDTO $data, array $filtros): VentasDTO
    {
        if (!$data instanceof VentasDTO) {
            return $data;
        }

        if (isset($filtros['anio_general'])) {
            $data = $this->filtrarPorAnio($data, $filtros['anio_general']);
        }

        if (isset($filtros['mes_general'])) {
            $data = $this->filtrarPorMes($data, $filtros['mes_general']);
        }

        if (isset($filtros['visitadora_id'])) {
            $data = $this->filtrarPorVisitadora($data, $filtros['visitadora_id']);
        }

        if (isset($filtros['provincia_id'])) {
            $data = $this->filtrarPorProvincia($data, $filtros['provincia_id']);
        }

        if (isset($filtros['fecha_inicio_producto']) || isset($filtros['fecha_fin_producto'])) {
            $data = $this->filtrarPorFechasProducto($data, $filtros);
        }

        return $data;
    }

    /**
     * Aplica filtro por año específico
     */
    private function filtrarPorAnio(VentasDTO $data, int $anio): VentasDTO
    {
        // La lógica de filtrado por año ya está implementada en VentasDTO
        // Aquí podríamos hacer filtrado adicional si es necesario
        return $data;
    }

    /**
     * Aplica filtro por mes específico
     */
    private function filtrarPorMes(VentasDTO $data, int $mes): VentasDTO
    {
        // La lógica de filtrado por mes ya está implementada en VentasDTO
        // Aquí podríamos hacer filtrado adicional si es necesario
        return $data;
    }

    /**
     * Aplica filtro por visitadora específica
     */
    private function filtrarPorVisitadora(VentasDTO $data, int $visitadoraId): VentasDTO
    {
        // TODO: Implementar filtrado por visitadora específica
        return $data;
    }

    /**
     * Aplica filtro por provincia específica
     */
    private function filtrarPorProvincia(VentasDTO $data, int $provinciaId): VentasDTO
    {
        // TODO: Implementar filtrado por provincia específica
        return $data;
    }

    /**
     * Aplica filtro por fechas específicas para productos
     */
    private function filtrarPorFechasProducto(VentasDTO $data, array $filtros): VentasDTO
    {
        // Los filtros de fecha para productos ya se aplican en el constructor de VentasDTO
        // Este método existe para mantener consistencia con otros filtros
        // Si necesitamos filtrado adicional, se puede implementar aquí
        return $data;
    }

    /**
     * Obtiene los años disponibles para reportes
     *
     * @return array Lista de años disponibles
     */
    private function getAniosDisponibles(): array
    {
        $anioActual = date('Y');
        return range($anioActual - 5, $anioActual + 1);
    }

    /**
     * Genera una key única para cache basada en filtros
     *
     * @param array $filtros Filtros aplicados
     * @return string Key para cache
     */
    protected function generateCacheKey(array $filtros): string
    {
        return $this->cachePrefix . md5(serialize($filtros));
    }

    /**
     * Obtiene datos del cache si existen
     *
     * @param string $key Key del cache
     * @return VentasDTO|null Datos cacheados o null
     */
    protected function getFromCache(string $key): ?VentasDTO
    {
        return Cache::get($key);
    }

    /**
     * Guarda datos en cache
     *
     * @param string $key Key del cache
     * @param VentasDTO $data Datos a cachear
     */
    protected function saveToCache(string $key, VentasDTO $data): void
    {
        Cache::put($key, $data, $this->cacheTtl);
    }
}
