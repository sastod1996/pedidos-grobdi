<?php

namespace Database\Factories;

use App\Models\Muestras;
use App\Models\User;
use App\MuestraEstadoType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MuestrasEstado>
 */
class MuestrasEstadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'muestras_id' => Muestras::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'type' => fake()->randomElement(MuestraEstadoType::cases()),
            'comment' => fake()->optional()->sentence()
        ];
    }
}
