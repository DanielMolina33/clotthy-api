<?php

namespace App\Http\Controllers\country;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\countries\Countries;

class CountryController extends Controller {
    public function index(){
        $countries = Countries::where('estado', 1)->get();

        if(isset($countries)){
            $response = ['res' => ['data' => $countries],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los proveedores, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function create(){
        //
    }

    public function store(Request $req){
        //
    }

    public function show($id){
        //
    }

    public function edit($id){
        //
    }

    public function update(Request $req, $id){
        //
    }

    public function destroy($id){
        //
    }
}
