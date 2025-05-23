<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoVisita extends Model
{
    protected $table = 'estado_visita';
    public $timestamps = false;
    public function visitadoctor()
    {
        return $this->hasMany(VisitaDoctor::class);
    }
}
