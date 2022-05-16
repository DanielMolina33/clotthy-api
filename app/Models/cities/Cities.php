<?php

namespace App\Models\cities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model {
    use HasFactory;
    protected $table = "ciudades";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'iddepar',
        'nombreciudades',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

}
