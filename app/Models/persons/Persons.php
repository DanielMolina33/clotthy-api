<?php

namespace App\Models\persons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persons extends Model {
    use HasFactory;

    protected $table = "personas";
    public $timestamps = false;
    protected $fillable = [
        'tipodocumento',
        'idciudad',
        'genero',
        'nombres',
        'apellidos',
        'numerodocumento',
        'fechanacimiento',
        'avatar',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
