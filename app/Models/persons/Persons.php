<?php

namespace App\Models\persons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\addresses\Addresses;
use App\Models\employees\Employees;
use App\Models\phones\Phones;

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

    public function employee(){
        return $this->hasMany(Employees::class, 'idpersona');
    }

    public function address(){
        return $this->hasMany(Addresses::class, 'idpersona');
    }

    public function phone(){
        return $this->hasMany(Phones::class, 'idpersona');
    }
}
