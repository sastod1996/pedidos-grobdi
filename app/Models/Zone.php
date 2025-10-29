<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'confirmed',
    ];
    public function pedidos()
    {
        return $this->hasMany(Pedidos::class);
    }
    public function listas()
    {
        return $this->hasMany(Lista::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_zones');
    }

    public function distritos()
    {
        return $this->hasManyThrough(
            Distrito::class,
            Lista::class,
            'zone_id',
            null,
            'id',
            'id'
        );
    }

    public $timestamps = false;
}
