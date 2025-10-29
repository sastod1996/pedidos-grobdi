<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalNotReachedConfig extends Model
{
    /** @use HasFactory<\Database\Factories\GoalNotReachedConfigFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'state'
    ];
    protected $casts = [
        'state' => 'boolean',
    ];
    public function details()
    {
        return $this->hasMany(GoalNotReachedConfigDetail::class);
    }
    public function monthlyVisitorGoal()
    {
        return $this->hasMany(MonthlyVisitorGoal::class);
    }
}
