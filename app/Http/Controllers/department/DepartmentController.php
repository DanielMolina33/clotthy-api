<?php

namespace App\Http\Controllers\department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\departments\Departments;

class DepartmentController extends Controller {
    public function index(Request $req){
		$countryId = $req->query('country_id');
		
		if($countryId){
			$departments = Departments::where('estado', 1)->where('idpais', $countryId)->get();
		} else {
			return response(['message' => 'country_id in url is required'], 400);
		}

        if(isset($departments)){
            $response = ['res' => ['data' => $departments],  'status' => 200];
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
