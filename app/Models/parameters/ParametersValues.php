<?php

namespace App\Models\persons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametersValues extends Model {
    use HasFactory;

    protected $table = "tipos";
    public $timestamps = false;
    protected $fillable = [
        'idTipo',
        'nombretipos',
        'descripciontipos',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
