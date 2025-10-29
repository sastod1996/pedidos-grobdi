<?php

namespace Database\Factories;

use App\Models\CategoriaDoctor;
use App\Models\CentroSalud;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'tipo_medico' => fake()->randomElement(['Prescriptor', 'Comprador']),
            'especialidad_id' => Especialidad::factory(),
            'centrosalud_id' => CentroSalud::factory(),
            'categoria_medico' => 'Visitador',
            'categoriadoctor_id' => CategoriaDoctor::factory(),
            'asignado_consultorio' => 0,
        ];
    }
}
