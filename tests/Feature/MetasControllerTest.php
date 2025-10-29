<?php

use App\Application\Services\Visitadoras\Metas\MetasService;
use App\Models\User;
use App\Models\Role;
use App\Models\View; // o el modelo que uses para los permisos
use App\Models\Doctor;
use App\Models\MonthlyVisitorGoal;
use App\Models\VisitorGoal;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::factory()->create(['id' => 1, 'name' => 'admin']);
    $this->visitadoraRole = Role::factory()->create(['id' => 6, 'name' => 'visitador']);

    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
});

it('displays the list of metas', function () {
    $url = 'visitadoras.metas';

    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);

    $metasServiceMock = mock(MetasService::class);

    $paginator = new LengthAwarePaginator(
        collect([
            [
                'id' => 1,
                'tipo_medico' => 'Prescriptor',
                'date' => [
                    'type' => 'month',
                    'value' => 10,
                    'year' => 2025
                ]
            ],
        ]),
        1,
        15,
        1,
        ['path' => '/visitadoras/metas']
    );

    $metasServiceMock->shouldReceive('getListOfMetas')
        ->once()
        ->with()
        ->andReturn($paginator);

    // Act & Assert
    $this->instance(MetasService::class, $metasServiceMock);

    $response = $this->actingAs($this->admin)->get(route($url));

    $response->assertOk()
        ->assertViewIs('visitadoras.metas.index')
        ->assertViewHas('listOfMetas', $paginator);
});

it('can display the metas form with visitadoras and tipo_medico list', function () {
    $url = 'visitadoras.metas.form';
    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);

    // 3. Crear algunos datos de prueba
    User::factory()->count(5)->create(['role_id' => $this->visitadoraRole->id]);
    Doctor::factory()->create(['tipo_medico' => 'Cardiólogo']);
    Doctor::factory()->create(['tipo_medico' => 'Pediatra']);
    Doctor::factory()->create(['tipo_medico' => '']); // vacío, para probar filter()

    // 4. Actuar: hacer la petición como usuario autenticado
    $response = $this->actingAs($this->admin)->get(route($url));

    // 5. Afirmar
    $response->assertStatus(200);
    $response->assertViewIs($url);
    $response->assertViewHas('visitadoras', function ($visitadoras) {
        return $visitadoras->isNotEmpty();
    });
    $response->assertViewHas('tipoMedicoList', function ($tipoMedicoList) {
        return is_array($tipoMedicoList)
            && in_array('Cardiólogo', $tipoMedicoList)
            && in_array('Pediatra', $tipoMedicoList)
            && !in_array('', $tipoMedicoList); // asegurar que los vacíos se filtraron
    });
});

it('returns chart data successfully when visitor goal exists', function () {
    $url = 'visitadoras.metas.details';
    $view = View::factory()->create(['url' => $url]);
    $this->adminRole->views()->attach($view);

    $metasServiceMock = mock(MetasService::class);
    $this->instance(MetasService::class, $metasServiceMock);

    $visitadora = User::factory()->create(['role_id' => 6]);
    $monthlyGoal = MonthlyVisitorGoal::factory()->create([
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
        'tipo_medico' => 'especialista',
    ]);

    $visitorGoal = VisitorGoal::factory()->create([
        'user_id' => $visitadora->id,
        'monthly_visitor_goal_id' => $monthlyGoal->id,
        'goal_amount' => 100000,
        'debited_amount' => 20000,
    ]);

    $mockedChartData = [
        'total_pedidos' => 5,
        'total_amount_without_igv' => '820.00',
        'faltante_para_meta' => '180.00',
        'avance_meta_general' => 82,
        'commissioned_amount' => Money::of(20000, 'PEN')->getAmount()->__toString(),
    ];

    $mockedDoctorsData = collect([
        [
            'id' => 1,
            'name' => 'Juan Pérez López',
            'total_sub_total' => 1000,
            'monto_sin_igv' => 820,
        ],
        [
            'id' => 2,
            'name' => 'María Gómez',
            'total_sub_total' => 500,
            'monto_sin_igv' => 410,
        ],
    ]);

    $metasServiceMock
        ->shouldReceive('getDataForChart')
        ->once()
        ->with(Mockery::on(function ($arg) use ($visitorGoal) {
            return $arg->id === $visitorGoal->id;
        }))
        ->andReturn($mockedChartData);

    $metasServiceMock
        ->shouldReceive('getPedidosDoctorStatsByMonthlyVisitorGoal')
        ->once()
        ->with($monthlyGoal->id)
        ->andReturn($mockedDoctorsData);

    // Act
    $response = $this->actingAs($this->admin)
        ->postJson(route($url, ['visitorGoalId' => $visitorGoal->id]));

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Datos para chart obtenidos.',
            'chart-data' => $mockedChartData,
            'doctors-data' => $mockedDoctorsData->toArray(),
        ]);
});

it('returns 500 error when visitor goal does not exist', function () {
    $url = 'visitadoras.metas.details';

    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);

    $nonExistentId = 999999;

    $response = $this->actingAs($this->admin)->postJson(route($url, ['visitorGoalId' => $nonExistentId]));

    $response->assertStatus(500)
        ->assertJson([
            'success' => false,
            'message' => "No query results for model [App\Models\VisitorGoal] {$nonExistentId}",
        ]);
});