<?php

namespace App\Application\Services\Reportes;

use App\Application\DTOs\Reportes\ReporteDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Clase base para todos los servicios de reportes
 *
 * Esta clase proporciona funcionalidad común a todos los servicios de reportes:
 * - Caching automático
 * - Validación de filtros
 * - Gestión de configuración
 * - Helpers para fechas y meses
 *
 * Los servicios específicos deben extender esta clase e implementar
 * los métodos abstractos.
 */
abstract class BaseReporteService
{
    protected string $cachePrefix = 'reporte_';  
    protected int $cacheTtl = 3600;         
}