<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PedidosDeliveryState extends Model
{

    const UPDATED_AT = null;

    protected $table = 'pedidos_delivery_state';

    protected $appends = ['created_at_formatted'];

    protected $casts = [
        'datetime_foto_domicilio' => 'datetime',
        'datetime_foto_entrega' => 'datetime',
    ];

    protected $fillable = [
        'pedido_id',
        'state',
        'motorizado_id',
        'observacion'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedidos::class);
    }

    public function location()
    {
        return $this->morphMany(Location::class, 'locationable');
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null;
    }

    public function getFotoData(string $locationType)
    {
        $location = $this->location()->firstWhere('type', $locationType);

        if (!$location) {
            return null;
        }

        return [
            'lat' => $location->latitude,
            'lng' => $location->longitude
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'motorizado_id');
    }
}
