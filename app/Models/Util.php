<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Util extends Model
{
    protected $table = 'utiles';
    public $timestamps = false;
    protected $fillable = [
        'articulo_id',
        'precio',
    ];
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function ultimoLote()
    {
        return $this->hasOne(Lote::class, 'articulo_id', 'articulo_id')->latestOfMany();
    }

}
