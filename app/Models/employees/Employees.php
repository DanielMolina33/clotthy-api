<?php

namespace App\Models\employees;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Employees extends Authenticatable {
    use HasFactory, HasApiTokens;
    protected $table = "usuarios";
    public $timestamps = false;
    protected $hidden = ['contrasena', 'intentos', 'estado'];
    protected $fillable = [
        'nombreusuario',
        'idersona',
        'email',
        'contrasena',
        'intentos',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
