<?php

namespace App\Models\companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\socialmedia\SocialMedia;
use App\Models\cities\Cities;
use App\Models\phones\Phones;

class Companies extends Model {
    use HasFactory;
    protected $table = "empresas";
    public $timestamps = false;
    protected $hidden = ['estado'];
    protected $fillable = [
        'idciudad',
        'nombreempresa',
        'logo',
        'nitempresa',
        'estado',
        'fechacreacion',
        'fechamodificacion'
    ];

    public function socialMedia(){
        return $this->hasMany(SocialMedia::class, 'idempresa');
    }

    public function phone(){
        return $this->hasMany(Phones::class, 'idempresa');
    }
}
