<?php

namespace App\Http\Controllers\person;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\persons\Persons;

class PersonController extends Controller {
    function newPerson(Request $req){
        dd("HOLA PRROS DESDE PERSONCONTROLLER");
    }
}
