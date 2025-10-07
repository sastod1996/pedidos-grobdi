<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_softlynn',
        'email',
        'password',
        'active',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function pedidos()
    {
        return $this->hasMany(Pedidos::class); // Un post tiene muchos comentarios
    }
    public function pedidosVisitadora()
    {
        return $this->hasMany(Pedidos::class, 'visitadora_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class); 
    }
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'user_zones');
    }
    
    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }
    
}
