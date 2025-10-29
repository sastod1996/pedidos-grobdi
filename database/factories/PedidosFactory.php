<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoalNotReachedConfig>
 */
class PedidosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $doctor = Doctor::factory()->create();
        $user = User::factory(['role_id' => Role::factory()->create()->id])->create();
        return [
            'orderId' => fake()->numerify('ORD-######'),
            'customerName' => fake()->name(),
            'customerNumber' => fake()->phoneNumber(),
            'doctorName' => $doctor->name,
            'id_doctor' => $doctor->id,
            'address' => fake()->address(),
            'reference' => fake()->sentence(3),
            'district' => fake()->city(),
            'prize' => fake()->randomFloat(2, 50, 500),
            'paymentStatus' => 'pending',
            'deliveryDate' => now()->addDays(3),
            'user_id' => $user->id,
            'visitadora_id' => $user->id,
            'zone_id' => Zone::factory()->create()->id,
            'deliveryStatus' => 'pending',
        ];
    }
}
