<?php

namespace App\Models\products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\products\Inventory;
use App\Models\comments\Comments;
use App\Models\ratings\Ratings;

class Products extends Model{
    use HasFactory;

    protected $table = "productos";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'color',
        'talla',
        'subcategoria',
        'referenciaprod',
        'nombreprod',
        'descripcionprod',
        'stock',
        'preciounitario',
        'preciofinal',
        'preciodescuento',
        'porcentajedescuento',
        'existenciaprod',
        'total',
        'imgprod1',
        'imgprod2',
        'imgprod3',
        'imgprod4',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'color' => 'int',
        'talla' => 'int',
        'subcategoria' => 'int',
    ];

    public function inventory(){
        return $this->hasMany(Inventory::class, 'idprod');
    }

    public function comment(){
        return $this->hasMany(Comments::class, 'idprod');
    }

    public function rating(){
        return $this->hasMany(Ratings::class, 'idprod');
    }
}
