<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Casts\ReadablePercentageCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorGoal extends Model
{
    /** @use HasFactory<\Database\Factories\VisitorGoalFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'monthly_visitor_goal_id',
        'goal_amount',
        'commission_percentage',
        'debited_amount',
        'debited_datetime',
        'debit_comment',
    ];
    protected $casts = [
        'goal_amount' => MoneyCast::class,
        'debited_amount' => MoneyCast::class,
        'debited_datetime' => 'datetime',
    ];
    public function visitadora()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function monthlyVisitorGoal()
    {
        return $this->belongsTo(MonthlyVisitorGoal::class, 'monthly_visitor_goal_id');
    }
    public function goalNotReachedConfig()
    {
        return $this->hasOneThrough(
            GoalNotReachedConfig::class,
            MonthlyVisitorGoal::class,
            'id',
            'id',
            'monthly_visitor_goal_id',
            'goal_not_reached_config_id'
        );
    }
    public function getFormattedCommissionPercentageAttribute()
    {
        return (new ReadablePercentageCast())->get($this, 'commission_percentage', $this->commission_percentage, []);
    }
}
