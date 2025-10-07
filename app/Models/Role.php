<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description'];
    public function users()
    {
        return $this->hasMany(User::class); // Un post tiene muchos comentarios
    }
    public function modules() {
        return $this->belongsToMany(Module::class, 'roles_modules');
    }

    public function views() {
        return $this->belongsToMany(View::class, 'roles_views');
    }
}
