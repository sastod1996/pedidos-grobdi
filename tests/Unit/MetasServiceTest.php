<?php

use App\Application\Services\Pedidos\PedidosService;
use App\Application\Services\Visitadoras\Metas\MetasService;
use App\Models\DetailPedidos;
use App\Models\Doctor;
use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use App\Models\MonthlyVisitorGoal;
use App\Models\Pedidos;
use App\Models\Role;
use App\Models\User;
use App\Models\VisitorGoal;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::factory()->create(['id' => 1, 'name' => 'admin']);
    $this->role = Role::factory()->create(['id' => 6, 'name' => 'visitador']);

    $this->service = new MetasService(new PedidosService());

    $this->goalNotReachedConfig = GoalNotReachedConfig::factory()->create();
    $this->visitadoras = User::factory()->count(3)->create(['role_id' => 6]);
    $this->user = User::factory()->create(['role_id' => 1]);
});

it('crea metas generales correctamente', function () {
    $month = '2025-10';
    $data = [
        'month' => $month,
        'tipo_medico' => 'Prescriptor',
        'is_general_goal' => true,
        'goal_amount' => 1025.50,
        'commission_percentage' => 4.2,
    ];

    $result = $this->service->create($data);

    expect($result)
        ->toBeInstanceOf(MonthlyVisitorGoal::class)
        ->tipo_medico->toBe('Prescriptor');
    expect($result->start_date)->toBe(Carbon::parse($month)->startOfMonth()->toDateString());
    expect($result->end_date)->toBe(Carbon::parse($month)->endOfMonth()->toDateString());
    expect($result->visitorGoals)->toHaveCount(3);
    expect($result->visitorGoals->first()->goal_amount)->toBeInstanceOf(Money::class);
    expect($result->visitorGoals->first()->goal_amount->getAmount()->__toString())->toBe('1025.50');
    expect($result->visitorGoals->last()->commission_percentage)->toBe(0.042);
    expect($result->visitorGoals->pluck('user_id'))
        ->toMatchArray([$this->visitadoras[0]->id, $this->visitadoras[1]->id, $this->visitadoras[2]->id]);
});

it('crea metas individuales correctamente', function () {
    $data = [
        'month' => '2025-10',
        'tipo_medico' => 'Comprador',
        'is_general_goal' => false,
        'visitor_goals' => [
            [
                'user_id' => $this->visitadoras[0]->id,
                'goal_amount' => 800,
                'commission_percentage' => 4.5,
            ],
            [
                'user_id' => $this->visitadoras[1]->id,
                'goal_amount' => 1200,
                'commission_percentage' => 6.0,
            ],
            [
                'user_id' => $this->visitadoras[2]->id,
                'goal_amount' => 1300,
                'commission_percentage' => 6.3,
            ],
        ],
    ];

    $this->service->create($data);

    $this->assertDatabaseHas('monthly_visitor_goals', [
        'tipo_medico' => 'Comprador',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
    ]);

    foreach ($data['visitor_goals'] as $item) {
        $this->assertDatabaseHas('visitor_goals', [
            'user_id' => $item['user_id'],
            'goal_amount' => $item['goal_amount'],
            'commission_percentage' => $item['commission_percentage'] / 100,
        ]);
    }
});

it('lanza excepción si ya existe una meta para el mismo mes y tipo de médico', function () {
    MonthlyVisitorGoal::factory()->create([
        'tipo_medico' => 'cardiologo',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
    ]);

    $data = [
        'month' => '2025-10',
        'tipo_medico' => 'cardiologo',
        'is_general_goal' => true,
        'goal_amount' => 1000,
        'commission_percentage' => 5.0,
    ];

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Ya existe una meta para este mes y este tipo de doctor.');

    $this->service->create($data);
});

it('lanza excepción si se intenta crear meta general sin visitadoras', function () {
    User::where('role_id', 6)->delete();

    $data = [
        'month' => '2025-10',
        'tipo_medico' => 'dermatologo',
        'is_general_goal' => true,
        'goal_amount' => 1000,
        'commission_percentage' => 5.0,
    ];

    $this->expectException(Illuminate\Validation\ValidationException::class);
    $this->expectExceptionMessage('No hay visitadoras para asignar las metas.');

    $this->service->create($data);
});

it('lanza excepción si no existe una GoalNotReachedConfig activa', function () {
    $this->goalNotReachedConfig->update(['state' => false]);

    $data = [
        'month' => '2025-10',
        'tipo_medico' => 'pediatra',
        'is_general_goal' => true,
        'goal_amount' => 1000,
        'commission_percentage' => 5.0,
    ];

    $this->expectException(Illuminate\Database\Eloquent\ModelNotFoundException::class);

    $this->service->create($data);
});

it('obtiene información relevante a la Meta Mensual', function () {
    $startDate = now()->startOfMonth();
    $endDate = now()->endOfMonth();

    $monthlyVisitorGoal = MonthlyVisitorGoal::factory()->create([
        'tipo_medico' => 'Prescriptor',
        'start_date' => $startDate->toDateString(),
        'end_date' => $endDate->toDateString(),
    ]);

    $expectedTotal = 1000 + 23.5 + 10.2;

    $prescriptorDoctors = Doctor::factory()->count(2)->create(['tipo_medico' => 'Prescriptor']);
    Doctor::factory()->count(2)->create(['tipo_medico' => 'Comprador']);

    foreach ($prescriptorDoctors as $doctor) {
        $pedido1 = Pedidos::factory()->create([
            'id_doctor' => $doctor->id,
            'status' => true,
            'created_at' => now(),
        ]);
        $pedido2 = Pedidos::factory()->create([
            'id_doctor' => $doctor->id,
            'status' => true,
            'created_at' => now(),
        ]);

        DetailPedidos::factory()->create([
            'pedidos_id' => $pedido1->id,
            'status' => true,
            'sub_total' => 1000,
        ]);
        DetailPedidos::factory()->create([
            'pedidos_id' => $pedido1->id,
            'status' => true,
            'sub_total' => 23.5,
        ]);
        DetailPedidos::factory()->create([
            'pedidos_id' => $pedido2->id,
            'status' => true,
            'sub_total' => 10.2,
        ]);
    }

    $result = $this->service->getPedidosDoctorStatsByMonthlyVisitorGoal($monthlyVisitorGoal->id);

    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($result)->toHaveCount(2);

    foreach ($result as $item) {
        expect($item)->toHaveKeys(['id', 'name', 'total_sub_total', 'monto_sin_igv']);
        expect($item->total_sub_total)->toBeNumeric();
        expect($item->total_sub_total)->toBe($expectedTotal);
    }
});

it('Calculate Commission Rate', function () {
    $visitadora = User::factory()->create(['name' => 'Ana Pérez', 'role_id' => 6]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.0,
        'final_percentage' => 0.69,
        'commission' => 0.01,
    ]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.7,
        'final_percentage' => 0.9,
        'commission' => 0.03,
    ]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.91,
        'final_percentage' => 0.9999,
        'commission' => 0.05,
    ]);
    $monthlyGoal = MonthlyVisitorGoal::factory()->create(['goal_not_reached_config_id' => $this->goalNotReachedConfig->id]);
    $visitorGoal = VisitorGoal::factory()->create([
        'monthly_visitor_goal_id' => $monthlyGoal->id,
        'user_id' => $visitadora->id,
        'goal_amount' => 1000,
        'commission_percentage' => 0.1,
    ]);

    $calculateCommissionRate = (new ReflectionClass($this->service))->getMethod('calculateCommissionRate');
    $calculateCommissionRate->setAccessible(true);

    $result = $calculateCommissionRate->invoke($this->service, 0.8, $visitorGoal->commission_percentage, $monthlyGoal->goal_not_reached_config_id);

    expect($result)->toBeFloat(0.3);
});

it('returns a list of visitor goal metrics for a given meta id', function () {
    $visitadora = User::factory()->create(['name' => 'Ana Pérez', 'role_id' => 6]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.0,
        'final_percentage' => 0.69,
        'commission' => 0.01,
    ]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.7,
        'final_percentage' => 0.9,
        'commission' => 0.03,
    ]);
    GoalNotReachedConfigDetail::factory()->create([
        'goal_not_reached_config_id' => $this->goalNotReachedConfig->id,
        'initial_percentage' => 0.91,
        'final_percentage' => 0.9999,
        'commission' => 0.05,
    ]);
    $monthlyGoal = MonthlyVisitorGoal::factory()->create(['goal_not_reached_config_id' => $this->goalNotReachedConfig->id]);
    $visitorGoal = VisitorGoal::factory()->create([
        'monthly_visitor_goal_id' => $monthlyGoal->id,
        'user_id' => $visitadora->id,
        'goal_amount' => 1000,
        'commission_percentage' => 0.1,
    ]);

    $pedidos = Pedidos::factory()->count(8)->create([
        'visitadora_id' => $visitadora->id,
        'user_id' => $visitadora->id,
    ]);

    foreach ($pedidos as $pedido) {
        DetailPedidos::factory()->count(2)->create([
            'pedidos_id' => $pedido->id,
            'cantidad' => 5,
            'unit_prize' => 10,
            'sub_total' => 50
        ]);
    }

    // Mock del servicio para devolver un subtotal fijo
    $this->mock(PedidosService::class, function ($mock) {
        $mock->shouldReceive('calculateTotalSubTotal')
            ->andReturn(800.0);
    });

    $result = $this->service->getListOfVisitorGoalByMetaId($monthlyGoal->id);

    expect($result)->toHaveCount(1);
    expect($result[0])->toMatchArray([
        'id' => $visitorGoal->id,
        'visitadora' => [
            'id' => $visitadora->id,
            'name' => 'Ana Pérez',
        ],
        'goal_amount' => '1000.00',
        'porcentaje_actual' => 80.0, // (800 / 1000) * 100
        'comision_actual' => 3.0, // 3% según la configuración
        'total_sub_total_sin_igv' => '656.00', // 800 * 0.82
        'monto_comisionado' => '19.68', // 656 * 0.03
        'debited_amount' => 'Sin monto debitado',
        'debited_datetime' => 'No se ha debitado aún'
    ]);
});

it('returns empty list when monthly goal has no visitor goals', function () {
    $monthlyGoal = MonthlyVisitorGoal::factory()->create();

    $result = $this->service->getListOfVisitorGoalByMetaId($monthlyGoal->id);

    expect($result)->toBeEmpty();
});

it('throws ModelNotFoundException when meta id does not exist', function () {
    $nonExistentId = 999999;

    $this->service->getListOfVisitorGoalByMetaId($nonExistentId);
})->throws(ModelNotFoundException::class);

it('permite crear metas para el mismo mes con distinto tipo de médico', function () {
    // Primero, crea una meta para un tipo de médico en octubre 2025
    $data1 = [
        'month' => '2025-10',
        'tipo_medico' => 'Prescriptor',
        'is_general_goal' => true,
        'goal_amount' => 1000,
        'commission_percentage' => 5.0,
    ];

    $result1 = $this->service->create($data1);

    expect($result1)->toBeInstanceOf(MonthlyVisitorGoal::class);
    expect($result1->tipo_medico)->toBe('Prescriptor');

    // Ahora, intenta crear otra meta en el mismo mes pero con otro tipo de médico
    $data2 = [
        'month' => '2025-10',
        'tipo_medico' => 'Comprador',
        'is_general_goal' => true,
        'goal_amount' => 1200,
        'commission_percentage' => 6.0,
    ];

    $result2 = $this->service->create($data2);

    expect($result2)->toBeInstanceOf(MonthlyVisitorGoal::class);
    expect($result2->tipo_medico)->toBe('Comprador');

    // Verifica que ambas metas existen en la base de datos
    $this->assertDatabaseHas('monthly_visitor_goals', [
        'tipo_medico' => 'Prescriptor',
        'start_date' => '2025-10-01',
    ]);

    $this->assertDatabaseHas('monthly_visitor_goals', [
        'tipo_medico' => 'Comprador',
        'start_date' => '2025-10-01',
    ]);

    // Asegura que son dos registros distintos
    expect(MonthlyVisitorGoal::where('start_date', '2025-10-01')->count())->toBe(2);
});