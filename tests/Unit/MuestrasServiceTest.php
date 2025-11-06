<?php

use App\Application\Services\Muestras\MuestrasService;
use App\Models\Muestras;
use App\Models\MuestrasEstado;
use App\Models\Role;
use App\Models\User;
use App\MuestraEstadoType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role_id' => Role::factory()->create(['id' => 1, 'name' => 'admin'])->id]);

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

// Tests de Estado
test('Lanza excepción con APROVE_COORDINADOR si ya existe ese estado', function () {
    $service = app(MuestrasService::class);

    $reflection = new ReflectionClass($service);
    $metodoPrivado = $reflection->getMethod('assertValidTransition');
    $metodoPrivado->setAccessible(true);

    $muestra = Muestras::factory()->create();

    $estado1 = MuestrasEstado::factory()->create([
        'muestras_id' => $muestra->id,
        'user_id' => $this->user->id,
        'type' => MuestraEstadoType::APROVE_COORDINADOR
    ]);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('La muestra ya fue aprobada por la Coordinadora de Líneas.');

    $metodoPrivado->invoke($service, $muestra, MuestraEstadoType::APROVE_COORDINADOR);
});

test('Lanza excepción con APROVE_JEFE_COMERCIAL si ya existe ese estado', function () {
    $service = app(MuestrasService::class);

    $reflection = new ReflectionClass($service);
    $metodoPrivado = $reflection->getMethod('assertValidTransition');
    $metodoPrivado->setAccessible(true);

    $muestra = Muestras::factory()->create();

    $estado1 = MuestrasEstado::factory()->create([
        'muestras_id' => $muestra->id,
        'user_id' => $this->user->id,
        'type' => MuestraEstadoType::APROVE_JEFE_COMERCIAL
    ]);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('La muestra ya fue aprobada por el Jefe Comercial.');

    $metodoPrivado->invoke($service, $muestra, MuestraEstadoType::APROVE_JEFE_COMERCIAL);
});

test('Lanza excepción con SET_PRICE si se intenta saltar APROVE_JEFE_OPERACIONES', function () {
    $service = app(MuestrasService::class);

    $reflection = new ReflectionClass($service);
    $metodoPrivado = $reflection->getMethod('assertValidTransition');
    $metodoPrivado->setAccessible(true);

    $muestra = Muestras::factory()->create();

    $estado1 = MuestrasEstado::factory()->create([
        'muestras_id' => $muestra->id,
        'user_id' => $this->user->id,
        'type' => MuestraEstadoType::SET_PRICE
    ]);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Se requiere de aprobación del Jefe de Operaciones.');

    $metodoPrivado->invoke($service, $muestra, MuestraEstadoType::PRODUCED);
});

test('Lanza excepción con si se intenta colocar un estado anterior al actual del flujo', function () {
    $service = app(MuestrasService::class);

    $reflection = new ReflectionClass($service);
    $metodoPrivado = $reflection->getMethod('assertValidTransition');
    $metodoPrivado->setAccessible(true);

    $muestra = Muestras::factory()->create();

    $estado1 = MuestrasEstado::factory()->create([
        'muestras_id' => $muestra->id,
        'user_id' => $this->user->id,
        'type' => MuestraEstadoType::APROVE_COORDINADOR
    ]);
    $estado2 = MuestrasEstado::factory()->create([
        'muestras_id' => $muestra->id,
        'user_id' => $this->user->id,
        'type' => MuestraEstadoType::APROVE_JEFE_COMERCIAL
    ]);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('La muestra ya fue aprobada por el Jefe Comercial.');

    $metodoPrivado->invoke($service, $muestra, MuestraEstadoType::APROVE_COORDINADOR);
});
