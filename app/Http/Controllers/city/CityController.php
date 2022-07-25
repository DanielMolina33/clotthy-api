<?php

namespace App\Http\Controllers\city;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\cities\Cities;

class CityController extends Controller {
    public function index(Request $req){
        $departmentId = $req->query('department_id');
        $cityId = $req->query('city_id');
		
		if($cityId && $departmentId){
			$cities = Cities::where('estado', 1)
            ->where('iddepar', $departmentId)
            ->where('id', $cityId)
            ->first();
		} else if($departmentId) {
			$cities = Cities::where('estado', 1)->where('iddepar', $departmentId)->get();
		} else {
            return response(['message' => 'department_id or city_id in url is required'], 400);
        }

        if(isset($cities)){
            $response = ['res' => ['data' => $cities],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los departamentos'], 'status' => 400];
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
