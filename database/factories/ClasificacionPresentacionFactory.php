<?php

namespace Database\Factories;

use App\Models\Clasificacion;
use App\Models\Muestras;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muestras>
 */
class ClasificacionPresentacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => fake()->randomNumber(),
            'clasificacion_id' => Clasificacion::factory()->create()->id
        ];
    }
}
