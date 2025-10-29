<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'icon',
        'url',
        'state',
        'module_id',
        'is_menu'
    ];
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_views');
    }
}
