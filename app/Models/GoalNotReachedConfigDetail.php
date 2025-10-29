<?php

namespace App\Models;

use App\Casts\ReadablePercentageCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalNotReachedConfigDetail extends Model
{
    /** @use HasFactory<\Database\Factories\GoalNotReachedConfigDetailFactory> */
    use HasFactory;
    protected $fillable = [
        'goal_not_reached_config_id',
        'initial_percentage',
        'final_percentage',
        'commission',
    ];
    public function notReachedConfig()
    {
        return $this->belongsTo(GoalNotReachedConfig::class);
    }
    public function getFormattedInitialPercentageAttribute()
    {
        return (new ReadablePercentageCast())->get($this, 'initial_percentage', $this->initial_percentage, []);
    }
    public function getFormattedFinalPercentageAttribute()
    {
        return (new ReadablePercentageCast())->get($this, 'final_percentage', $this->final_percentage, []);
    }
    public function getFormattedCommissionAttribute()
    {
        return (new ReadablePercentageCast())->get($this, 'commission', $this->commission, []);
    }
}
