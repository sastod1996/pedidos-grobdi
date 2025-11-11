<?php

namespace Database\Factories;

use App\Models\Clasificacion;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muestras>
 */
class ClasificacionFactory extends Factory
{
    protected $model = Clasificacion::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_clasificacion' => fake()->word(),
            'unidad_de_medida_id' => UnidadMedida::factory()->create()->id
        ];
    }
}
