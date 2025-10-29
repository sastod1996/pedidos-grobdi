<?php

namespace Database\Factories;

use App\Models\Clasificacion;
use App\Models\Doctor;
use App\Models\Muestras;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muestras>
 */
class MuestrasFactory extends Factory
{
    protected $model = Muestras::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $tipoFrasco = fake()->randomElement(Muestras::TIPOS_FRASCO);

        return [
            'nombre_muestra' => fake()->words(3, true),
            'observacion' => fake()->sentence,
            'cantidad_de_muestra' => fake()->numberBetween(1, 100),
            'precio' => fake()->randomFloat(2, 10, 1000),
            'lab_state' => false,
            'clasificacion_id' => 3,
            'datetime_scheduled' => now()->addDays(2),
            'datetime_delivered' => null,
            'tipo_frasco' => $tipoFrasco,
            'aprobado_jefe_comercial' => false,
            'aprobado_coordinadora' => false,
            'aprobado_jefe_operaciones' => false,
            'name_doctor' => fake()->name,
            'id_doctor' => 1,
            'state' => true,
            'created_by' => User::factory(),
            'foto' => null,
            'clasificacion_presentacion_id' => $tipoFrasco === 'Frasco Original' ? 3 : null,
            'delete_reason' => null,
        ];
    }
}
