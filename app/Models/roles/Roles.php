<?php

namespace App\Models\roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\roles\Modules;

class Roles extends Model {
    use HasFactory;
    protected $table = "roles";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'nombrerol',    
        'descripcionrol',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    public function moduleRole(){
        return $this->belongsToMany(Modules::class, 'modulos_roles', 'idroles', 'idmodulos')
        ->withPivot('id', 'crear', 'actualizar', 'leer', 'eliminar', 'estado', 'fechacreacion', 'fechamodificacion');
    }
}
