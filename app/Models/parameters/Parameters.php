<?php

namespace App\Models\persons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameters extends Model {
    use HasFactory;

    protected $table = "tipo";
    public $timestamps = false;
    protected $fillable = [
        'nombretipo',
        'descripciontipo',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];
}
