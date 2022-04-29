<?php

namespace App\Models\addresses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adresses extends Model {
    use HasFactory;
    protected $table = "direcciones";
    public $timestamps = false;
    protected $fillable = [
        'tipodireccion',
        'idpersona',
        'idproveedor',
        'direccion',
        'codigopostal',
        'complementos',
        'estadodireccion',
        'fechacreacion',
        'fechamodificacion'
    ];
}
