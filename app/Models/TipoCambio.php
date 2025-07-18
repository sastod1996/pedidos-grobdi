<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    use HasFactory;

    protected $table = 'tipo_cambio';
    public $timestamps = false;
    protected $fillable = [
        'tipo_moneda_id',
        'valor_compra',
        'valor_venta',
        'fecha',
    ];

    public function tipoMoneda()
    {
        return $this->belongsTo(TipoMoneda::class, 'tipo_moneda_id');
    }
}
