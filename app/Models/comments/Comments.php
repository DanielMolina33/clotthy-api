<?php

namespace App\Models\comments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model {
    use HasFactory;

    protected $table = "comentarios";
    public $timestamps = false; 
    protected $hidden = ['estado'];
    protected $fillable = [
        'idprod',
        'idcliente',
        'comentario',
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
