<?php

namespace Database\Factories;

use App\Models\Clasificacion;
use App\Models\Doctor;
use App\Models\Muestras;
use App\Models\Role;
use App\Models\UnidadMedida;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muestras>
 */
class UnidadMedidaFactory extends Factory
{
    protected $model = UnidadMedida::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'nombre_unidad_de_medida' => fake()->name()
        ];
    }
}
