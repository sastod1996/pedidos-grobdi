<?php

namespace Database\Factories;

use App\Models\MonthlyVisitorGoal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitorGoal>
 */
class VisitorGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create([
                'role_id' => Role::factory()->create()->id
            ])->id,
            'monthly_visitor_goal_id' => MonthlyVisitorGoal::factory()->create()->id,
            'goal_amount' => fake()->randomFloat(2, 50, 500),
            'commission_percentage' => fake()->randomFloat(4, 0.05, 0.4),
        ];
    }
}
