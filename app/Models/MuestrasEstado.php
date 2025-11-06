<?php

namespace App\Models;

use App\MuestraEstadoType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuestrasEstado extends Model
{
    /** @use HasFactory<\Database\Factories\MuestrasEstadoFactory> */
    use HasFactory;
    const UPDATED_AT = null;
    protected $fillable = [
        'muestras_id',
        'user_id',
        'type',
        'comment',
    ];
    protected $casts = [
        'type' => MuestraEstadoType::class
    ];
    public function muestra()
    {
        return $this->belongsTo(Muestras::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
