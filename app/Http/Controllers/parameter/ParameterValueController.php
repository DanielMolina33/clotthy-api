<?php

namespace App\Http\Controllers\parameter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\parameters\ParametersValues;


class ParameterValueController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();
    }

    public function index(Request $req){
        $pagination = env('PAGINATION_PER_PAGE');
		$parameterId = $req->query('parameter_id');
		
		if($parameterId){
			$parameter = ParametersValues::where('estado', 1)->where('idtipo', $parameterId)->simplePaginate($pagination);
		} else {
			return response(['message' => 'parameter_id in url is required'], 400);
		}

        if(isset($parameter)){
            $response = ['res' => ['data' => $parameter],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los valores de los parametros'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function store(Request $req){
        $validator = $this->validateFields->validate($req, ['id_type', 'parameter_name', 'parameter_desc']);
        if($validator) return response($validator['res'], $validator['status']);

        $parameter = ParametersValues::create([
            'idtipo' => $req->id_type,
            'nombretipos' => ucfirst(strtolower($req->parameter_name)),
            'descripciontipos' => $req->parameter_desc,
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        if(isset($parameter)){
            $response = ['res' => ['message' => 'El valor del parametro fue creado correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al crear el valor del parametro, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function show(Request $req, $id){
        //
    }

    public function update(Request $req, $id){
        if ($req->isMethod('PUT')){
            $validator = $this->validateFields->validate($req, ['id_type', 'parameter_name', 'parameter_desc']);
            if($validator) return response($validator['res'], $validator['status']);

            $parameter = ParametersValues::where('id', $id)->update([
				'idtipo' => $req->id_type,
				'nombretipos' => ucfirst(strtolower($req->parameter_name)),
				'descripciontipos' => $req->parameter_desc,
				'fechamodificacion' => date('Y-m-d')
			]);

            if(isset($parameter)){
				$response = ['res' => ['message' => 'El valor del parametro fue actualizado correctamente'], 'status' => 200];
			} else {
				$response = ['res' => ['message' => 'Hubo un error al actualizar el valor del parametro, intentalo de nuevo'], 'status' => 400];
			}

            return response($response['res'], $response['status']);

        } else if ($req->isMethod('PATCH')){
            $response = $this->destroy($req, $id);

            return response($response['res'], $response['status']);
        }

    }

    public function destroy($req, $id){
        $parameter = ParametersValues::where('id', $id)->update([
            'estado' => 0,
            'fechamodificacion' => date('Y-m-d')
        ]);

        if(isset($parameter)){
            return ['res' => ['message' => 'El valor del parametro fue eliminado correctamente'], 'status' => 200];
        } else {
            return ['res' => ['message' => 'Hubo un error al eliminar el valor del parametro, intentalo de nuevo'], 'status' => 400];
        }
    }
}
