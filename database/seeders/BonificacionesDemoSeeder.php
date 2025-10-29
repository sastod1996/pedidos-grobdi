<?php

namespace Database\Seeders;

use App\Models\MonthlyVisitorGoal;
use App\Models\User;
use App\Models\VisitorGoal;
use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BonificacionesDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Find or create the visitadora user by email
            // Use the visitadora the user mentioned
            $email = 'visitadora.sur@grobdi.com';
            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::factory()->create([
                    'name' => 'visitadora norte',
                    'email' => $email,
                    'password' => bcrypt('12345678'),
                    'active' => 1,
                    'role_id' => 6,
                ]);
            }

            // Ensure there is an active GoalNotReachedConfig (required by monthly_visitor_goals)
            $gnr = GoalNotReachedConfig::where('state', true)->first();
            if (! $gnr) {
                $gnr = GoalNotReachedConfig::create(['name' => 'Default ranges', 'state' => true]);
                // create a simple detail range: 0-50% => 0% commission, 50-100% => 50% commission (example)
                GoalNotReachedConfigDetail::create([
                    'goal_not_reached_config_id' => $gnr->id,
                    'initial_percentage' => 0.0000,
                    'final_percentage' => 0.4999,
                    'commission' => 0.0000,
                ]);
                GoalNotReachedConfigDetail::create([
                    'goal_not_reached_config_id' => $gnr->id,
                    'initial_percentage' => 0.5000,
                    'final_percentage' => 0.9999,
                    'commission' => 0.5000,
                ]);
            }

            // Create a MonthlyVisitorGoal for the current month if not exists
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end = Carbon::now()->endOfMonth()->toDateString();

            $monthly = MonthlyVisitorGoal::firstOrCreate([
                'start_date' => $start,
                'end_date' => $end,
            ], [
                'tipo_medico' => 'prescriptor',
                'goal_not_reached_config_id' => $gnr->id,
            ]);

            // Create a VisitorGoal for this visitadora if not exists
            $existing = VisitorGoal::where('user_id', $user->id)
                ->where('monthly_visitor_goal_id', $monthly->id)
                ->first();

            if (! $existing) {
                $vg = VisitorGoal::create([
                    'user_id' => $user->id,
                    'monthly_visitor_goal_id' => $monthly->id,
                    'goal_amount' => 15000.00,
                    // commission_percentage is decimal(4,4) so store as fraction (3.5% -> 0.0350)
                    'commission_percentage' => 0.0350,
                    'debited_amount' => 0.00,
                    'debit_comment' => null,
                ]);
            }

            DB::commit();
            $this->command->info('Bonificaciones demo seeded for user: ' . $email);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Failed seeding bonificaciones demo: ' . $e->getMessage());
            throw $e;
        }
    }
}
