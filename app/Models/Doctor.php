<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctor';

    protected $fillable = [
        'especialidad_id',
        'name_secretariat',
    ];
    public const TIPOMEDICO = [
        'Comprador','Prescriptor','En Proceso'
    ];
    public function days()
    {
        return $this->belongsToMany(Day::class, 'doctor_day')
                    ->withPivot('turno');
    }
    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
    public function categoriadoctor()
    {
        return $this->belongsTo(CategoriaDoctor::class);
    }
    public function centrosalud()
    {
        return $this->belongsTo(CentroSalud::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
