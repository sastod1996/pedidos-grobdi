<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    use HasFactory;
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
        'deliveryStatus',
        'status',
    ];

    protected $casts = [
        'last_data_update' => 'datetime',
        'deliveryDate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', true);
        });
    }

    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active');
    }

    public function scopeOnlyInactive(Builder $query): Builder
    {
        return $query->withInactive()->where('status', false);
    }
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
        return $this->hasMany(DetailPedidos::class)->where('status', true);
    }

    public function detailpedidosWithInactive()
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
