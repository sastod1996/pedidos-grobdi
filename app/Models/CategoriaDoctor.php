<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDoctor extends Model
{
    use HasFactory;
    protected $table = 'categoria_doctor';
    protected $fillable = ['name', 'prioridad', 'monto_inicial', 'monto_final'];

    public $timestamps = false;
    public function doctores()
    {
        return $this->hasMany(Doctor::class);
    }
}
