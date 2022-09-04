<?php

namespace App\Models\ratings;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ratings extends Model {
    use HasFactory;

    protected $table = "calificaciones";
    public $timestamps = false; 
    protected $hidden = ['estado'];
    protected $fillable = [
        'idprod',
        'idcliente',
        'puntajecalificacion',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idprod' => 'int',
        'idcliente' => 'int'
    ];
}
