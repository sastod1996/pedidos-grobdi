<?php

namespace App\Models;

use App\MuestraEstadoType;
use App\Traits\Model\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muestras extends Model
{
    use WithoutTimestamps;
    use HasFactory;

    protected $dates = ['datetime_scheduled', 'datetime_delivered'];
    protected $table = 'muestras';

    // Eliminamos 'unidad_de_medida_id' del fillable ya que no existirá
    protected $fillable = [
        'nombre_muestra',
        'observacion',
        'cantidad_de_muestra',
        'precio',
        'lab_state', /**  @deprecated instead use logic based on MuestraEstados model */
        'clasificacion_id', // Mantenemos esta relación
        'datetime_scheduled',
        'datetime_delivered',
        'id_tipo_muestra', // nuevo campo
        'tipo_frasco', // antiguo tipo_muestra
        'aprobado_jefe_comercial', /**  @deprecated instead use logic based on MuestraEstados model */
        'aprobado_coordinadora', /**  @deprecated instead use logic based on MuestraEstados model */
        'aprobado_jefe_operaciones', /**  @deprecated instead use logic based on MuestraEstados model */
        'name_doctor',
        'id_doctor',
        'state',
        'created_by',
        'foto',
        'clasificacion_presentacion_id',
        'delete_reason',
        'updated_by',
        'comentarios'
    ];

    public const TIPOS_FRASCO = ['Frasco Original', 'Frasco Muestra'];

    // Relación con Clasificacion
    public function clasificacion()
    {
        return $this->belongsTo(Clasificacion::class);
    }

    // Accesor para obtener la unidad de medida a través de la clasificación
    public function getUnidadDeMedidaAttribute()
    {
        return $this->clasificacion ? $this->clasificacion->unidadMedida : null;
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tipoMuestra()
    {
        return $this->belongsTo(TipoMuestra::class, 'id_tipo_muestra');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id_doctor');
    }

    public function clasificacionPresentacion()
    {
        return $this->belongsTo(ClasificacionPresentacion::class, 'clasificacion_presentacion_id');
    }

    /* -------------------------------- Metodos y Variables relevante a los Estados ↓ -------------------------------- */

    // Lista de estados
    public function status()
    {
        return $this->hasMany(MuestrasEstado::class);
    }
    // Return latest MuestrasEstado inserted
    public function currentStatus()
    {
        return $this->hasOne(MuestrasEstado::class)->latestOfMany();
    }
    // Checks if the Entity has at least one of the MuestraEstadoType specified
    public function hasEvent(MuestraEstadoType $type): bool
    {
        return $this->status()->where('type', $type)->exists();
    }
    // Checks if the Entity has at least one of the MuestraEstadoType specified
    public function scopeWithEvent($query, MuestraEstadoType $type)
    {
        return $query->whereExists(function ($q) use ($type) {
            $q->selectRaw(1)
                ->from('muestras_estados')
                ->whereColumn('muestras_estados.muestra_id', 'muestras.id')
                ->where('type', $type);
        });
    }
    // Checks if the Entity doesn't have at least one of the MuestraEstadoType specified
    public function scopeWithoutEvent($query, MuestraEstadoType $type)
    {
        return $query->whereNotExists(function ($q) use ($type) {
            $q->selectRaw(1)
                ->from('muestras_estados')
                ->whereColumn('muestras_estados.muestra_id', 'muestras.id')
                ->where('type', $type);
        });
    }

}
