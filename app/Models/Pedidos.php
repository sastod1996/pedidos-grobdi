<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $fillable = [
        'orderId',
        'nroOrder',
        'customerName',
        'customerNumber',
        'doctorName',
        'id_doctor',
        'address',
        'turno',
        'reference',
        'district',
        'prize',
        'paymentStatus',
        'productionStatus',
        'accountingStatus',
        'deliveryDate',
        'detailMotorizado',
        'user_id',
        'visitadora_id',
        'zone_id',
        'voucher',
        'receta',
        'observacion_laboratorio',
        'fecha_reprogramacion',
        'last_data_update',
    ];

    protected $casts = [
        'last_data_update' => 'datetime',
        'deliveryDate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class); 
    }
    public function visitadora()
    {
        return $this->belongsTo(User::class, 'visitadora_id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class); 
    }
    public function detailpedidos()
    {
        return $this->hasMany(DetailPedidos::class);
    }
    
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id_doctor');
    }
    
    public function deliveryStates()
    {
        return $this->hasMany(PedidosDeliveryState::class, 'pedido_id');
    }
    
    public function currentDeliveryState()
    {
        return $this->hasOne(PedidosDeliveryState::class, 'pedido_id')->latestOfMany();
    }
}
