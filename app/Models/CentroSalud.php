<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroSalud extends Model
{
    use HasFactory;
    protected $table = 'centrosalud';
    protected $fillable = [
        'name',
        'description',
        'adress',
        'latitude',
        'longitude',
        'state'
    ];

    public $timestamps = false;

    public function doctores()
    {
        return $this->hasMany(Doctor::class);
    }
}
