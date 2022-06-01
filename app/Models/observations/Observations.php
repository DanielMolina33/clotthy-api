<?php

namespace App\Models\observations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observations extends Model {
    use HasFactory;

    protected $table = "observaciones";
    public $timestamps = false;
    protected $hidden = ['idpersona', 'identradaproductos', 'estado'];
    protected $fillable = [
        'idpersona',
        'identradaproductos',
        'observacion',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idpersona' => 'int',
        'identradaproductos' => 'int'
    ];
}
