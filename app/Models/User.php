<?php
// app/Models/User.php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;

class User extends Eloquent implements AuthenticatableContract, JWTSubject
{
    use Authenticatable, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasRole($role)
{
    $userRoles = is_array($this->roles) ? $this->roles : explode(',', $this->roles);
    return in_array($role, $userRoles);
}

    
public function showSensitiveFields()
{
    $this->setHidden([]); // Esto quitará temporalmente la ocultación de campos sensibles
    return $this;
}

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'email', 'password', 'role',
        'nombre', 'apellido_paterno', 'apellido_materno', 'edad', 'estado',
        'ocupacion', 'escolaridad'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function comentarios()
{
    return $this->hasMany(Comentario::class, 'usuario_id', '_id');
}

    // Campos y métodos específicos para ambos tipos de usuarios...
}
