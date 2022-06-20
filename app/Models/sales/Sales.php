<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\products\Products;

class Sales extends Model {
    use HasFactory;

    protected $table = "ventas";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'id',
        'idcliente',
        'metodopago',
        'referenciapago',
        'totalpago',
        'fechaestimada',
        'guiaenvio',
        'transportadora',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'string',
        'idcliente' => 'int'
    ];

    public function productSale(){
        return $this->belongsToMany(Products::class, 'productos_ventas', 'idventa', 'idprod')
        ->withPivot('id', 'cantidad');
    }
}
