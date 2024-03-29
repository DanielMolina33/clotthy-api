<?php

namespace App\Models\cities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\departments\Departments;

class Cities extends Model {
    use HasFactory;
    protected $table = "ciudades";
    public $timestamps = false;
    protected $hidden = ['estado', 'fechacreacion', 'fechamodificacion'];
    protected $fillable = [
        'iddepar',
        'costoenvios',
        'nombreciudades',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int',
        'iddepar' => 'int'
    ];

    public function department(){
        return $this->belongsTo(Departments::class, 'iddepar');
    }
}
