<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    // Nombre de la tabla (correcto según tu migración)
    protected $table = 'unidad_de_medida';

    // Campos asignables (correcto)
    protected $fillable = [
        'nombre_unidad_de_medida',
    ];

    // Relación con Clasificaciones (correcta)
    public function clasificaciones()
    {
        return $this->hasMany(Clasificacion::class);
    }
    //valida nombre 
    public static function rules()
    {
        return [
            'nombre_unidad_de_medida' => 'required|string|max:255|unique:unidad_de_medida'
        ];
    }

    // 2. Atributo para mostrar nombre formateado
    public function getNombreFormateadoAttribute()
    {
        return ucfirst(strtolower($this->nombre_unidad_de_medida));
    }

    // 3. Scope para búsquedas
    public function scopeSearch($query, $search)
    {
        return $query->where('nombre_unidad_de_medida', 'like', "%{$search}%");
    }
}