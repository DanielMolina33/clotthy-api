<?php

namespace App\Models\customers;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Customers extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $table = "clientes";
    public $timestamps = false;
    protected $fillable = [
        'nombreusuario',
        'idPersona',
        'email',
        'contrasena',
        'estado',
        'fechacreacion',
        'fechaactualizacion'
    ];
}
