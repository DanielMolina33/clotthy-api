<?php

namespace App\Models\phones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phones extends Model {
    use HasFactory;
    protected $table = "telefonos";
    public $timestamps = false;
    protected $fillable = [
        'tiponumero',
        'idproveedor',
        'idempresa',
        'idusuario',
        'numerotelefono',
        'idicativo',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
