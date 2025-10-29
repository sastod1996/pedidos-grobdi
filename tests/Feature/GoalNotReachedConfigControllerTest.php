<?php

use App\Application\Services\Visitadoras\Metas\GoalNotReachedConfigService;
use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use App\Models\Role;
use App\Models\User;
use App\Models\View;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::factory()->create(['id' => 1, 'name' => 'admin']);

    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
});

test('puede guardar una configuración nueva', function () {
    $url = 'visitadoras.metas.not-reached-config.store';

    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);

    $validatedData = [
        'name' => 'Config Q3 2025',
        'details' => [
            [
                'initial_percentage' => 0,
                'final_percentage' => 50,
                'commission' => 2.5,
            ],
            [
                'initial_percentage' => 50,
                'final_percentage' => 99.99,
                'commission' => 5.0,
            ],
        ],
    ];

    $mockService = mock(GoalNotReachedConfigService::class);
    $mockService->shouldReceive('create')
        ->once()
        ->with($validatedData)
        ->andReturn((object) [
            'id' => 1,
            'name' => 'Config Q3 2025',
            'details' => collect()
        ]);

    $this->instance(GoalNotReachedConfigService::class, $mockService);

    $response = $this->actingAs($this->admin)->postJson(route($url), $validatedData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Configuración para metas creada correctamente.',
        ]);
});

it('returns the goal not reached config with its details', function () {
    $url = 'visitadoras.metas.not-reached-config';

    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);


    $config = GoalNotReachedConfig::factory()->create([
        'name' => 'Active Conf',
        'state' => true,
    ]);

    $gnrcd1 = GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $config->id,
        'initial_percentage' => 0.0,
        'final_percentage' => 0.5,
        'commission' => 0.025,
    ]);

    $gnrcd2 = GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $config->id,
        'initial_percentage' => 0.51,
        'final_percentage' => 0.9999,
        'commission' => 0.05,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson(route($url, $config));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'state',
            'details' => [
                '*' => [
                    'id',
                    'initial_percentage',   // ← ahora es el valor formateado
                    'final_percentage',
                    'commission',
                ],
            ],
        ])
        ->assertJson([
            'id' => $config->id,
            'name' => 'Active Conf',
            'state' => true,
        ])
        ->assertJsonFragment([
            'id' => $gnrcd1->id,
            'initial_percentage' => '0.00',
            'final_percentage' => '50.00',
            'commission' => '2.50',
        ])
        ->assertJsonFragment([
            'id' => $gnrcd2->id,
            'initial_percentage' => '51.00',
            'final_percentage' => '99.99',
            'commission' => '5.00',
        ]);
});

it('returns 404 when the config does not exist', function () {
    $this->withoutMiddleware(\App\Http\Middleware\CheckPermission::class);

    $nonExistentId = 999999;

    $response = $this->actingAs($this->admin)
        ->getJson(route('visitadoras.metas.not-reached-config', $nonExistentId));

    $response->assertStatus(404);
});