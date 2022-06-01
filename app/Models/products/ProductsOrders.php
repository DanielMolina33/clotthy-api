<?php

namespace App\Models\products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsOrders extends Model {
    use HasFactory;

    protected $table = "productos_orden";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idorden',
        'nombreproducto',
        'cantidad',
    ];

    protected $casts = [
        'id' => 'int',
        'idorden' => 'int'
    ];
}
