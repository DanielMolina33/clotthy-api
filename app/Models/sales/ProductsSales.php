<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsSales extends Model {
    use HasFactory;

    protected $table = "productos_ventas";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'idventa',
        'idprod',
        'cantidad',
    ];

    protected $casts = [
        'id' => 'int',
        'idventa' => 'int',
        'idprod' => 'int'
    ];
}
