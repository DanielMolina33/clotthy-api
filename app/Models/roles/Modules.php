<?php

namespace App\Models\roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model {
    use HasFactory;
    protected $table = "modulos";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'nombremodulo',    
        'descripcionmodulo',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int'
    ];
}
