<?php

use App\Application\Services\Muestras\MuestrasService;
use App\Models\Muestras;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

beforeEach(function () {
    Storage::fake('public');
});

test('crea una muestra como ADMIN', function () {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role_id' => 1]);

    $data = [
        'nombre_muestra' => 'Muestra Test',
        'clasificacion_id' => 1,
        'cantidad_de_muestra' => 5,
        'tipo_frasco' => 'frasco muestra',
        'id_doctor' => 1,
        'created_by' => $admin->id
    ];

    $serviceMock = mock(MuestrasService::class);
    $serviceMock->shouldReceive('create');

    actingAs($admin)
        ->post(route('muestras.store'), $data)
        ->assertRedirect(route('muestras.index'))
        ->assertSessionHas('success');
});

test('crea una muestra como Jefe de Operaciones', function () {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role_id' => 7]);

    $data = [
        'nombre_muestra' => 'Muestra Test',
        'clasificacion_id' => 1,
        'cantidad_de_muestra' => 5,
        'tipo_frasco' => 'frasco muestra',
        'id_doctor' => 1,
        'created_by' => $admin->id
    ];

    $serviceMock = mock(MuestrasService::class);
    $serviceMock->shouldReceive('create');

    actingAs($admin)
        ->post(route('muestras.store'), $data)
        ->assertStatus(403);
});

test('actualiza muestra si no está aprobada', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role_id' => 1]);
    $muestra = Muestras::factory()->create();

    $serviceMock = mock(MuestrasService::class);
    $serviceMock->shouldReceive('update')->once()->andReturn($muestra);

    actingAs($user)
        ->put(route('muestras.update', $muestra), [
            'nombre_muestra' => 'Muestra Actualizada',
            'cantidad_de_muestra' => 20,
            'tipo_frasco' => 'frasco original',
            'clasificacion_id' => 3,
            'clasificacion_presentacion_id' => 4,
            'id_doctor' => 1,
        ])
        ->assertRedirect(route('muestras.index'))
        ->assertSessionHas('success');
});