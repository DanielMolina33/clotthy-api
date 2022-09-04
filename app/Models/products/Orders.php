<?php

namespace App\Models\products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\products\ProductsOrders;

class Orders extends Model {
    use HasFactory;

    protected $table = "ordenes";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idproveedor',
        'estadoorden',
        'emisororden',
        'descripcionorden',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idproveedor' => 'int'
    ];

    public function product(){
        return $this->hasMany(ProductsOrders::class, 'idorden');
    }
}
