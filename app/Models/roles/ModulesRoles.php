<?php

namespace App\Models\roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\roles\Roles;
use App\Models\roles\Modules;

class ModulesRoles extends Model {
    use HasFactory;
    protected $table = "modulos_roles";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idroles',
        'idmodulos',
        'crear',
        'actualizar',
        'leer',
        'eliminar',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idroles' => 'int',
        'idmodulos' => 'int'
    ];
}
