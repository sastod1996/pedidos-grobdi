<?php

namespace Database\Factories;

use App\Models\Clasificacion;
use App\Models\ClasificacionPresentacion;
use App\Models\Doctor;
use App\Models\Muestras;
use App\Models\Role;
use App\Models\UnidadMedida;
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

        $isProducedByLaboratory = fake()->boolean();
        $isAprovedByJefeOperaciones = fake()->boolean();
        $isAprovedByCoordinadora = fake()->boolean();
        $isAprovedByJefeComercial = fake()->boolean();
        $price = null;

        if ($isProducedByLaboratory) {
            $isAprovedByJefeOperaciones = true;
        }

        if ($isAprovedByJefeOperaciones) {
            $isAprovedByJefeComercial = true;
            $price = fake()->randomFloat(2, 10, 1000);
        }

        if ($isAprovedByJefeComercial) {
            $isAprovedByCoordinadora = true;

        }

        return [
            'nombre_muestra' => fake()->words(3, true),
            'observacion' => fake()->sentence,
            'cantidad_de_muestra' => fake()->numberBetween(1, 100),
            'precio' => $price,
            'lab_state' => $isProducedByLaboratory,
            'clasificacion_id' => Clasificacion::factory()->create()->id,
            'datetime_scheduled' => now()->addDays(2),
            'datetime_delivered' => null,
            'tipo_frasco' => $tipoFrasco,
            'aprobado_coordinadora' => $isAprovedByCoordinadora,
            'aprobado_jefe_comercial' => $isAprovedByJefeComercial,
            'aprobado_jefe_operaciones' => $isAprovedByJefeOperaciones,
            'name_doctor' => fake()->name,
            'id_doctor' => Doctor::factory()->create()->id,
            'state' => true,
            'created_by' => 1,
            'foto' => null,
            'clasificacion_presentacion_id' => $tipoFrasco === 'Frasco Original' ? ClasificacionPresentacion::factory()->create()->id : null,
            'delete_reason' => null,
        ];
    }
}
