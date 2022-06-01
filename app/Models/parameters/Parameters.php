<?php

namespace App\Models\parameters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\parameters\ParametersValues;

class Parameters extends Model {
    use HasFactory;

    protected $table = "tipo";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'nombretipo',
        'descripciontipo',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    protected $casts = [
        'id' => 'int'
    ];

    public function parameterValue(){
        return $this->hasMany(ParametersValues::class, 'idtipo');
    }
}
