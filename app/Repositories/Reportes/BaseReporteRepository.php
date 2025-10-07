<?php

namespace App\Repositories\Reportes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseReporteRepository implements ReporteRepositoryInterface
{
    protected Model $model;
    protected array $relations = [];
    protected array $selectFields = ['*'];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getDatosBasicos(): array
    {
        return [
            'total_registros' => $this->model->count(),
            'ultima_actualizacion' => $this->model->max('updated_at'),
        ];
    }

    public function getDatosFiltrados(array $filtros = []): Collection
    {
        $query = $this->model->select($this->selectFields);

        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        $query = $this->aplicarFiltros($query, $filtros);
        $query = $this->aplicarOrdenamiento($query, $filtros);

        return $query->get();
    }

    public function getEstadisticas(array $filtros = []): array
    {
        $query = $this->model->select($this->selectFields);

        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        $query = $this->aplicarFiltros($query, $filtros);

        return $this->calcularEstadisticas($query);
    }

    public function getFiltrosDisponibles(): array
    {
        return [
            'fecha_inicio' => [
                'type' => 'date',
                'label' => 'Fecha Inicio'
            ],
            'fecha_fin' => [
                'type' => 'date',
                'label' => 'Fecha Fin'
            ],
            'activo' => [
                'type' => 'boolean',
                'label' => 'Activo'
            ]
        ];
    }

    protected function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        // Filtros comunes
        if (isset($filtros['fecha_inicio'])) {
            $query->where('created_at', '>=', $filtros['fecha_inicio']);
        }

        if (isset($filtros['fecha_fin'])) {
            $query->where('created_at', '<=', $filtros['fecha_fin']);
        }

        if (isset($filtros['activo'])) {
            $query->where('activo', $filtros['activo']);
        }

        // Filtros específicos - las subclases pueden sobrescribir
        return $this->aplicarFiltrosEspecificos($query, $filtros);
    }

    protected function aplicarFiltrosEspecificos(Builder $query, array $filtros): Builder
    {
        return $query;
    }

    protected function aplicarOrdenamiento(Builder $query, array $filtros): Builder
    {
        $orden = $filtros['orden'] ?? 'created_at';
        $direccion = $filtros['direccion'] ?? 'desc';

        return $query->orderBy($orden, $direccion);
    }

    abstract protected function calcularEstadisticas(Builder $query): array;
}