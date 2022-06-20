<?php

namespace App\Models\departments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\countries\Countries;

class Departments extends Model {
    use HasFactory;

    protected $table = "departamentos";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'idpais',
        'nombredepar',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'idpais' => 'int'
    ];

    public function country(){
        return $this->belongsTo(Countries::class, 'idpais');
    }
}
