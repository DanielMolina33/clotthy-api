<?php

namespace App\Models\socialmedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model {
    use HasFactory;
    protected $table = "redessociales";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'idempresa',
        'nombrered',
        'enlacered',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

}
