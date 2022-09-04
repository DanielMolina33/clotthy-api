<?php

namespace App\Models\companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\socialmedia\SocialMedia;
use App\Models\phones\Phones;
use App\Models\cities\Cities;

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

    protected $casts = [
        'id' => 'int',
        'idciudad' => 'int'
    ];

    public function socialMedia(){
        return $this->hasMany(SocialMedia::class, 'idempresa');
    }

    public function phone(){
        return $this->hasMany(Phones::class, 'idempresa');
    }

    public function city(){
        return $this->belongsTo(Cities::class, 'idciudad');
    }
}
