<?php

namespace App\Models\parameters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\parameters\Parameters;

class ParametersValues extends Model {
    use HasFactory;

    protected $table = "tipos";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idtipo',
        'nombretipos',
        'descripciontipos',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idtipo' => 'int'
    ];
}
