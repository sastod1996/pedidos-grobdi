<?php

namespace Database\Factories;

use App\Models\Pedidos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoalNotReachedConfig>
 */
class DetailPedidosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrize = fake()->randomFloat(2, 1, 30);
        $cantidad = fake()->randomNumber();
        return [
            'pedidos_id' => Pedidos::factory()->create()->id,
            'articulo' => fake()->words(3, true),
            'cantidad' => $cantidad,
            'unit_prize' => $unitPrize,
            'sub_total' => $unitPrize * $cantidad,
        ];
    }
}
