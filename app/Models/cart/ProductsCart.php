<?php

namespace App\Models\cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductsCart extends Pivot {
    use HasFactory;

    protected $table = "productos_carrito";
    public $timestamps = false;
    protected $hidden = ['idprod', 'idcarrito', 'estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'idprod',
        'idcarrito',
        'cantidadproductos',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idprod' => 'int',
        'idcarrito' => 'int'
    ];
}
