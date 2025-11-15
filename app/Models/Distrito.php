<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
    public function doctores()
    {
        return $this->hasMany(Doctor::class);
    }
    public function listas()
    {
        return $this->belongsToMany(Lista::class, 'lista_distrito');
    }
    public $timestamps = false;
}
