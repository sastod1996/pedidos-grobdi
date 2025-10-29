<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function views()
    {
        return $this->hasMany(View::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_modules');
    }
}
