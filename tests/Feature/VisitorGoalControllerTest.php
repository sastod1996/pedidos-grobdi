<?php


use App\Models\Role;
use App\Models\User;
use App\Models\View;
use App\Models\VisitorGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::factory()->create(['id' => 1, 'name' => 'admin']);
    $this->visitadoraRole = Role::factory()->create(['id' => 6, 'name' => 'visitador']);

    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
});

it('update the debited amount', function () {
    $url = 'visitadoras.metas.update.debited-amount';

    $view = View::factory()->create(['url' => $url]);

    $this->adminRole->views()->attach($view);

    $visitadora = User::factory()->create(['role_id' => $this->visitadoraRole->id]);

    $visitorGoal = VisitorGoal::factory()->create([
        'user_id' => $visitadora->id,
    ]);

    $payload = [
        'debited_amount' => 150.50,
        'debited_datetime' => now()->toISOString(),
        'debit_comment' => 'Pago parcial',
    ];

    $response = $this->actingAs($this->admin)
        ->putJson(route($url, $visitorGoal->id), $payload);

    $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Monto debitado actualizado correctamente.',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'debited_amount',
                'debited_datetime',
                'debit_comment',
                'goal_amount',
                'commission_percentage',
                'user_id',
                'monthly_visitor_goal_id',
            ],
        ]);

    $this->assertDatabaseHas('visitor_goals', [
        'id' => $visitorGoal->id,
        'debited_amount' => '150.50',
        'debit_comment' => 'Pago parcial',
    ]);
});