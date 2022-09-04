<?php

namespace App\Models\cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\products\Products;
use App\Models\cart\ProductsCart;

class Cart extends Model {
    use HasFactory;

    protected $table = "carrito";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'idcliente',
        'totalpago',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idcliente' => 'int'
    ];

    public function productCart(){
        return $this->belongsToMany(Products::class, 'productos_carrito', 'idcarrito', 'idprod')
        ->using(ProductsCart::class)
        ->withPivot('id', 'cantidadproductos');
    }
}
