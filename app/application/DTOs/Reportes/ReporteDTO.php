<?php

namespace App\Application\DTOs\Reportes;

/**
 * Clase base para todos los DTOs de reportes
 *
 * Esta clase define la estructura común que deben tener todos los DTOs de reportes.
 * Contiene propiedades básicas como filtros aplicados, datos del reporte,
 * estadísticas calculadas, título y tipo de reporte.
 *
 * Los DTOs específicos (VentasDTO, DoctoresDTO, etc.) deben extender esta clase
 * y agregar sus propiedades específicas.
 */
class ReporteDTO
{
    // Propiedades básicas que todos los reportes deben tener
    public array $filtros;      // Filtros aplicados al reporte
    public array $datos;        // Datos principales del reporte
    public array $estadisticas; // Estadísticas calculadas
    public string $titulo;      // Título del reporte
    public string $tipo;        // Tipo de reporte (ventas, doctores, visitadoras)

    /**
     * Constructor de la clase base
     *
     * @param string $titulo Título del reporte
     * @param string $tipo Tipo de reporte
     * @param array $filtros Filtros aplicados (opcional)
     * @param array $datos Datos del reporte (opcional)
     * @param array $estadisticas Estadísticas (opcional)
     */
    public function __construct(
        string $titulo,
        string $tipo,
        array $filtros = [],
        array $datos = [],
        array $estadisticas = []
    ) {
        $this->titulo = $titulo;
        $this->tipo = $tipo;
        $this->filtros = $filtros;
        $this->datos = $datos;
        $this->estadisticas = $estadisticas;
    }

    /**
     * Convierte el DTO a un array para pasar a las vistas
     *
     * @return array Array con todos los datos del reporte
     */
    public function toArray(): array
    {
        return [
            'titulo' => $this->titulo,
            'tipo' => $this->tipo,
            'filtros' => $this->filtros,
            'datos' => $this->datos,
            'estadisticas' => $this->estadisticas,
        ];
    }
}