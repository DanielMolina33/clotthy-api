<?php

namespace App\Models\roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModulesRoles extends Model {
    use HasFactory;
    protected $table = "usuario_modulo_rol";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idusuario',    
        'idmodrol',
    ];

    protected $casts = [
        'id' => 'int',
        'idusuario' => 'int',
        'idmodrol' => 'int'
    ];
}
