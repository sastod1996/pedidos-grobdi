<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'state' => true,
            'module_id' => Module::factory(),
            'is_menu' => fake()->boolean(),
        ];
    }

    /**
     * Define una vista con atributos específicos (útil para seeders).
     */
    public function withAttributes(array $attributes): static
    {
        return $this->state($attributes);
    }
}
