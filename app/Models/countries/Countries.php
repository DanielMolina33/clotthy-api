<?php

namespace App\Models\countries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model {
    use HasFactory;

    protected $table = "paises";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'nombrepaises',
        'abreviaturapaises',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int'
    ];
}
