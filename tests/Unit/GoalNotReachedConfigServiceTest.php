<?php

use App\Application\Services\Visitadoras\Metas\GoalNotReachedConfigService;
use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(GoalNotReachedConfigService::class);
});

it('crea la configuración y sus detalles correctamente', function () {
    $data = [
        'name' => 'Configuración 1',
        'details' => [
            [
                'initial_percentage' => 0,
                'final_percentage' => 11.99,
                'commission' => 1.5,
            ],
            [
                'initial_percentage' => 12,
                'final_percentage' => 89,
                'commission' => 2,
            ],
            [
                'initial_percentage' => 90,
                'final_percentage' => 99.99,
                'commission' => 4,
            ],
        ]
    ];

    $this->service->create($data);

    $this->assertDatabaseHas('goal_not_reached_configs', [
        'name' => 'Configuración 1',
        'state' => true
    ]);

    $config = GoalNotReachedConfig::where('name', 'Configuración 1')->first();

    // Verificar que se crearon los 3 detalles
    expect(GoalNotReachedConfigDetail::count())->toBe(3);

    // Verificar cada detalle con los valores divididos entre 100
    $this->assertDatabaseHas('goal_not_reached_config_details', [
        'goal_not_reached_config_id' => $config->id,
        'initial_percentage' => 0.0,
        'final_percentage' => 0.1199,
        'commission' => 0.015,
    ]);

    $this->assertDatabaseHas('goal_not_reached_config_details', [
        'goal_not_reached_config_id' => $config->id,
        'initial_percentage' => 0.12,
        'final_percentage' => 0.89,
        'commission' => 0.02,
    ]);

    $this->assertDatabaseHas('goal_not_reached_config_details', [
        'goal_not_reached_config_id' => $config->id,
        'initial_percentage' => 0.90,
        'final_percentage' => 0.9999,
        'commission' => 0.04,
    ]);
});

test('rollback en caso de error durante la creación', function () {
    $data = [
        'name' => 'Configuración fallida',
        'details' => [
            [
                'initial_percentage' => 0,
                'final_percentage' => 50,
                'commission' => 5,
            ],
            [
                // Provocamos un error real de SQL (tipo de dato inválido)
                'initial_percentage' => 'INVALID',
                'final_percentage' => 60,
                'commission' => 10,
            ],
        ],
    ];

    $this->expectException(\Throwable::class);

    try {
        $this->service->create($data);
    } finally {
        $this->assertDatabaseMissing('goal_not_reached_configs', ['name' => 'Configuración fallida']);
        $this->assertDatabaseEmpty('goal_not_reached_config_details');
    }
});

it('desactiva las configuraciones anteriores al crear una nueva', function () {
    $old1 = GoalNotReachedConfig::factory()->create(['state' => true]);
    $old2 = GoalNotReachedConfig::factory()->create(['state' => true]);

    $data = [
        'name' => 'Nueva Configuración',
        'details' => [
            ['initial_percentage' => 0, 'final_percentage' => 50, 'commission' => 5],
        ],
    ];

    $this->service->create($data);

    $this->assertDatabaseHas('goal_not_reached_configs', [
        'name' => 'Nueva Configuración',
        'state' => true
    ]);

    $this->assertDatabaseHas('goal_not_reached_configs', [
        'id' => $old1->id,
        'state' => false,
    ]);

    $this->assertDatabaseHas('goal_not_reached_configs', [
        'id' => $old2->id,
        'state' => false,
    ]);
});
