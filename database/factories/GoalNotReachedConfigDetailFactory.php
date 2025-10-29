<?php

namespace Database\Factories;

use App\Models\GoalNotReachedConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoalNotReachedConfigDetail>
 */
class GoalNotReachedConfigDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_not_reached_config_id' => GoalNotReachedConfig::factory(),
            'initial_percentage' => fake()->randomFloat(4, 0.0, 0.5),
            'final_percentage' => fake()->randomFloat(4, 0.0, 0.5),
            'commission' => fake()->randomFloat(4, 0.05, 0.15),
        ];
    }
}
