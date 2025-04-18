<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPedidos extends Model
{
    protected $fillable = [
        'pedidos_id',
        'articulo',
        'cantidad',
        'unit_prize',
        'sub_total',
    ];
    public function pedido()
    {
        return $this->belongsTo(Pedidos::class); 
    }
}
