<?php

namespace App\Models\products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\observations\Observations;

class Inventory extends Model {
    use HasFactory;

    protected $table = "entrada_productos";
    public $timestamps = false; 
    protected $hidden = ['estado'];
    protected $fillable = [
        'idprod',
        'idorden',
        'cantidad',
        'precioproveedor',
        'porcentajeganancia',
        'total',
        'urlfactura',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idprod' => 'int',
        'idorden' => 'int'
    ];

    public function observation(){
        return $this->hasMany(Observations::class, 'identradaproductos');
    }
}
