<?php

namespace App\Models\phones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\parameters\ParametersValues;

class Phones extends Model {
    use HasFactory;
    protected $table = "telefonos";
    public $timestamps = false;
    protected $hidden = ['idproveedor', 'idempresa', 'idpersona', 'estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'tiponumero',
        'idproveedor',
        'idempresa',
        'idpersona',
        'numerotelefono',
        'indicativo',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    function parameterValue(){
        return $this->hasMany(ParametersValues::class, 'tiponumero');
    }
}
