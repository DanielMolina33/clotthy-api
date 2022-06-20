<?php

namespace App\Models\suppliers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\phones\Phones;
use App\Models\addresses\Addresses;

class Suppliers extends Model {
    use HasFactory;

    protected $table = "proveedores";
    public $timestamps = false; 
    protected $hidden = ['estado'];
    protected $fillable = [
        'tipodocumento',
        'idciudades',
        'nombreproveedor',
        'numerodocumento',
        'descripcion',
        'correoproveedor',
        'representante',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'tipodocumento' => 'int',
        'idciudades' => 'int'
    ];
    
    public function phone(){
        return $this->hasMany(Phones::class, 'idproveedor');
    }
    
    public function address(){
        return $this->hasMany(Addresses::class, 'idproveedor');
    }
}
