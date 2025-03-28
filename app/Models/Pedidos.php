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
        'zone_id',
        'voucher',
        'receta',
    ];
    public function user()
    {
        return $this->belongsTo(User::class); 
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class); 
    }
    public function detailpedidos()
    {
        return $this->hasMany(DetailPedidos::class);
    }
}
