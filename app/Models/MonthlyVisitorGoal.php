<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyVisitorGoal extends Model
{
    /** @use HasFactory<\Database\Factories\MonthlyVisitorGoalFactory> */
    use HasFactory;
    protected $fillable = [
        'goal_not_reached_config_id',
        'tipo_medico',
        'start_date',
        'end_date',
    ];
    protected $appends = ['month'];
    public function visitorGoals()
    {
        return $this->hasMany(VisitorGoal::class);
    }
    public function notReachedConfig()
    {
        return $this->belongsTo(GoalNotReachedConfig::class);
    }
    protected function month(): Attribute
    {
        return Attribute::get(function () {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

            if ($start->year === $end->year && $start->month === $end->month) {
                return [
                    'type' => 'month',
                    'value' => $start->month,
                    'year' => $start->year
                ];
            }
            return [
                'type' => 'custom_range',
                'start_date' => $start,
                'end_date' => $end
            ];
        });
    }
}
