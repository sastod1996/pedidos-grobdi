<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPedidos extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedidos_id',
        'articulo',
        'cantidad',
        'unit_prize',
        'tecnico_produccion',
        'estado',
        'sub_total',
        'estado_produccion',
        'usuario_produccion_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
    public function pedido()
    {
        return $this->belongsTo(Pedidos::class, 'pedidos_id');
    }
    public function usuario_produccion()
    {
        return $this->belongsTo(User::class, 'usuario_produccion_id');
    }
}
