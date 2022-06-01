<?php

namespace App\Models\employees;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Models\roles\UserModulesRoles;
use App\Models\roles\Roles;

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

    protected $casts = [
        'id' => 'int',
        'idpersona' => 'int'
    ];

    public function userModuleRole(){
        return $this->belongsToMany(Roles::class, 'usuario_modulo_rol', 'idusuario', 'idmodrol');
    }
}
