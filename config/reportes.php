<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Reportes
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada para todos los reportes del sistema.
    | Define tipos de reportes, permisos, caché y otras configuraciones.
    |
    */

    'tipos' => [
        'ventas' => [
            'nombre' => 'Reporte de Ventas',
            'modelo' => 'App\Models\Venta',
            'servicio' => 'App\Application\Services\Reportes\VentasReporteService',
            'dto' => 'App\Application\DTOs\Reportes\VentasDTO',
            'permisos' => ['ver-reportes-ventas'],
            'cache_ttl' => 3600, // 1 hora
        ],
        'doctores' => [
            'nombre' => 'Reporte de Doctores',
            'modelo' => 'App\Models\Doctor',
            'servicio' => 'App\Application\Services\Reportes\DoctoresReporteService',
            'dto' => 'App\Application\DTOs\Reportes\DoctoresDTO',
            'permisos' => ['ver-reportes-doctores'],
            'cache_ttl' => 3600,
        ],
        'visitadoras' => [
            'nombre' => 'Reporte de Visitadoras',
            'modelo' => 'App\Models\Visitadora',
            'servicio' => 'App\Application\Services\Reportes\VisitadorasReporteService',
            'dto' => 'App\Application\DTOs\Reportes\VisitadorasDTO',
            'permisos' => ['ver-reportes-visitadoras'],
            'cache_ttl' => 3600,
        ],
    ],

    'cache' => [
        'prefix' => 'reporte_',
        'ttl' => env('REPORTES_CACHE_TTL', 3600), // segundos
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

    'excel' => [
        'disk' => 'public',
        'path' => 'reportes/',
        'queue' => env('REPORTES_QUEUE', 'default'),
    ],

    'filtros_comunes' => [
        'fecha_inicio' => [
            'type' => 'date',
            'label' => 'Fecha Inicio',
            'required' => false,
        ],
        'fecha_fin' => [
            'type' => 'date',
            'label' => 'Fecha Fin',
            'required' => false,
        ],
        'anio' => [
            'type' => 'select',
            'label' => 'Año',
            'options' => 'getAniosDisponibles',
            'required' => false,
        ],
        'mes' => [
            'type' => 'select',
            'label' => 'Mes',
            'options' => 'getMeses',
            'required' => false,
        ],
    ],

    'export_formats' => [
        'excel' => 'xlsx',
        'csv' => 'csv',
        'pdf' => 'pdf',
    ],

    'dashboard' => [
        'widgets_por_pagina' => 6,
        'refresh_interval' => 300, // segundos
    ],
];