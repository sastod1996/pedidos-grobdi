<?php

namespace Database\Factories;

use App\Models\GoalNotReachedConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlyVisitorGoal>
 */
class MonthlyVisitorGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_medico' => 'Prescriptor',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'goal_not_reached_config_id' => GoalNotReachedConfig::factory(),
        ];
    }
}
