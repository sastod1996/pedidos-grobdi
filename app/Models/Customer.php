<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'document_number',
        'name',
        'first_last_name',
        'second_last_name',
        'email',
        'phone',
        'phone_2',
        'notes',
        'state',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this?->first_last_name} {$this?->second_last_name}";
    }
}
