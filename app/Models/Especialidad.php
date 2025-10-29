<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;
    protected $table = 'especialidad';
    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;

    public function doctores()
    {
        return $this->hasMany(Doctor::class);
    }
}
