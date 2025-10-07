<?php

namespace App\Application\Services\Reportes;

use App\Application\DTOs\Reportes\DoctoresDTO;
use App\Application\DTOs\Reportes\ReporteDTO;

/**
 * Servicio específico para reportes de doctores
 *
 * Este servicio maneja toda la lógica de negocio relacionada con reportes de doctores:
 * - Creación del DTO de doctores
 * - Definición de filtros válidos para doctores
 * - Aplicación de filtros específicos (por año, tipo de doctor, doctor específico)
 * - Validación de datos
 *
 * Extiende BaseReporteService para heredar funcionalidad común como caching.
 */
class DoctoresReporteService extends BaseReporteService
{
    // Prefijo específico para cache de doctores
    protected string $cachePrefix = 'doctores_reporte_';

    /**
     * Nota: Se deja el constructor vacío porque la lógica de consultas se movió al DTO (DoctoresDTO::buildFromFiltros)
     * para estandarizar según solicitud del usuario.
     */
    public function __construct() {}

    /**
     * Obtiene los datos del reporte aplicando filtros
     *
     * @param array $filtros Filtros a aplicar al reporte
     * @return DoctoresDTO Datos estructurados del reporte
     */
    public function getData(array $filtros = []): DoctoresDTO
    {
        $filtrosProcesados = $this->procesarFiltros($filtros);
        
        // Generar key para cache
        $cacheKey = $this->generateCacheKey($filtrosProcesados);
        
        // Intentar obtener del cache
        if ($data = cache()->get($cacheKey)) {
            return $data;
        }

        // Si no está en cache, crear los datos
        $data = $this->createReporteData($filtrosProcesados);
        
        // Guardar en cache
        cache()->put($cacheKey, $data, $this->cacheTtl);
        
        return $data;
    }

    /**
     * Genera una key única para cache basada en filtros
     *
     * @param array $filtros Filtros aplicados
     * @return string Key para cache
     */
    private function generateCacheKey(array $filtros): string
    {
        return $this->cachePrefix . md5(serialize($filtros));
    }

    /**
     * Crea el DTO específico para datos de doctores
     *
     * @param array $filtros Filtros aplicados
     * @return DoctoresDTO DTO con datos de doctores
     */
    protected function createReporteData(array $filtros = []): DoctoresDTO
    {
        // Ahora toda la construcción y consultas se realizan dentro del DTO
        return DoctoresDTO::buildFromFiltros($filtros);
    }

    /**
     * Obtiene los años disponibles para filtrar
     *
     * @return array Lista de años disponibles
     */
    public function getAniosDisponibles(): array
    {
        $anioActual = (int) date('Y');
        $años = [];
        
        for ($i = $anioActual; $i >= $anioActual - 4; $i--) {
            $años[] = $i;
        }
        
        return $años;
    }

    /**
     * Obtiene los tipos de médico disponibles
     *
     * @return array Lista de tipos de médico
     */
    public function getTiposMedicoDisponibles(): array
    {
        return ['Comprador', 'Prescriptor', 'En Proceso'];
    }

    /**
     * Procesa filtros antes de aplicarlos
     *
     * @param array $filtros Filtros crudos
     * @return array Filtros procesados
     */
    public function procesarFiltros(array $filtros): array
    {
        $filtrosProcesados = [];

        // Nuevos filtros de rango de fechas (inputs con sufijo _tipo_doctor desde la vista)
        if (!empty($filtros['fecha_inicio_tipo_doctor'])) {
            $fecha = $this->sanearFecha($filtros['fecha_inicio_tipo_doctor']);
            if ($fecha) {
                $filtrosProcesados['fecha_inicio'] = $fecha . ' 00:00:00';
            }
        }
        if (!empty($filtros['fecha_fin_tipo_doctor'])) {
            $fecha = $this->sanearFecha($filtros['fecha_fin_tipo_doctor']);
            if ($fecha) {
                $filtrosProcesados['fecha_fin'] = $fecha . ' 23:59:59';
            }
        }

        // Compatibilidad: si no hay rango pero sí año/mes antiguos
        if (empty($filtrosProcesados['fecha_inicio']) && empty($filtrosProcesados['fecha_fin'])) {
            if (!empty($filtros['anio_tipo_doctor'])) {
                $anio = (int) $filtros['anio_tipo_doctor'];
                if ($anio > 1900) {
                    $mes = null;
                    if (!empty($filtros['mes']) && is_numeric($filtros['mes']) && $filtros['mes'] >= 1 && $filtros['mes'] <= 12) {
                        $mes = (int) $filtros['mes'];
                    }
                    if ($mes) {
                        $filtrosProcesados['fecha_inicio'] = sprintf('%04d-%02d-01 00:00:00', $anio, $mes);
                        // Ultimo día del mes
                        $ultimoDia = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
                        $filtrosProcesados['fecha_fin'] = sprintf('%04d-%02d-%02d 23:59:59', $anio, $mes, $ultimoDia);
                    } else {
                        // Año completo
                        $filtrosProcesados['fecha_inicio'] = sprintf('%04d-01-01 00:00:00', $anio);
                        $filtrosProcesados['fecha_fin'] = sprintf('%04d-12-31 23:59:59', $anio);
                    }
                }
            }
        }

        // Tipo de médico
        if (!empty($filtros['tipo_medico']) && in_array($filtros['tipo_medico'], $this->getTiposMedicoDisponibles())) {
            $filtrosProcesados['tipo_medico'] = $filtros['tipo_medico'];
        }

        return $filtrosProcesados;
    }

    private function sanearFecha(string $fecha): ?string
    {
        // Aceptamos formato YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        return null;
    }
}