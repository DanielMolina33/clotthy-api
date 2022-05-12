<?php

namespace App\Models\pqrsf;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\parameters\ParametersValues;

class Pqrsf extends Model {
    use HasFactory;

    protected $table = "pqrsf";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idpersona',
        'estadosolicitudpqrsf',
        'tiposolicitudpqrsf',
        'tipodocumento',
        'asunto',
        'descripcion',
        'nombres',
        'apellidos',
        'email',
        'numerodocumento',
        'imgayuda',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
