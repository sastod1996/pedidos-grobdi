<?php

namespace App\Application\Services\Visitadoras\Metas;

use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use Illuminate\Support\Facades\DB;

class GoalNotReachedConfigService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // name is optional; if not provided generate a default name
            $name = $data['name'] ?? ('config_autogen_' . now()->format('Ymd_His'));
            $notReachedDetails = $data['details'] ?? [];

            GoalNotReachedConfig::query()->update(['state' => false]);

            $goalNotReachedConfig = GoalNotReachedConfig::create(['name' => $name]);

            // Se dividen entre 100 para guardar en la base de datos, ej: 52.55% => 0,5255
            foreach ($notReachedDetails as $row) {
                GoalNotReachedConfigDetail::create([
                    'goal_not_reached_config_id' => $goalNotReachedConfig->id,
                    'initial_percentage' => $row['initial_percentage'] / 100,
                    'final_percentage' => $row['final_percentage'] / 100,
                    'commission' => $row['commission'] / 100,
                ]);
            }

            return $goalNotReachedConfig->load('details');
        });
    }
}
