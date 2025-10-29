<?php

namespace App\Models;

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
        'lab_state',
        'clasificacion_id', // Mantenemos esta relación
        'datetime_scheduled',
        'datetime_delivered',
        'id_muestra', // nuevo campo
        'tipo_frasco', // antiguo tipo_muestra
        'aprobado_jefe_comercial',
        'aprobado_coordinadora',
        'aprobado_jefe_operaciones',
        'name_doctor',
        'id_doctor',
        'state',
        'created_by',
        'foto',
        'clasificacion_presentacion_id',
        'delete_reason'
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
}
