<?php

namespace App\Http\Controllers\parameter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\parameters\Parameters;


class ParameterController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();
    }

    public function index(){
        $pagination = env('PAGINATION_PER_PAGE');
        $parameter = Parameters::where('estado', 1)->simplePaginate($pagination);

        if(isset($parameter)){
            $response = ['res' => ['data' => $parameter],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los parametros'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function store(Request $req){
        $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc']);
        if($validator) return response($validator['res'], $validator['status']);

        $parameter = Parameters::create([
            'nombretipo'=> ucfirst(strtolower($req->parameter_name)),
            'descripciontipo'=> $req->parameter_desc,
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        if(isset($parameter)){
            $response = ['res' => ['message' => 'El parametro fue creado correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al crear el parametro, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function show(Request $req, $id){
        //
    }

    public function update(Request $req, $id){
        if ($req->isMethod('PUT')){
            $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc']);
            if($validator) return response($validator['res'], $validator['status']);

            $parameter = Parameters::where('id', $id)->update([
                'nombretipo'=> ucfirst(strtolower($req->parameter_name)),
                'descripciontipo'=> $req->parameter_desc,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($parameter)){
                $response = ['res' => ['message' => 'El parametro fue actualizado correctamente'], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al actualizar el parametro, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);
        }else if ($req->isMethod('PATCH')){
            $response = $this->destroy($req, $id);

            return response($response['res'], $response['status']);
        }

    }

    public function destroy($req, $id){
        $parameter = Parameters::where('id', $id)->first();

        if($parameter){
            $parameter->update([
                'estado' => 0,
                'fechamodificacion' => date('Y-m-d')
            ]);

            $parameter->parameterValue()->where('idtipo', $id)->update([
                'estado' => 0,
                'fechamodificacion' => date('Y-m-d')
            ]);
        }

        if(isset($parameter)){
            return ['res' => ['message' => 'El parametro fue eliminado correctamente'], 'status' => 200];
        } else {
            return ['res' => ['message' => 'Hubo un error al eliminar el parametro, intentalo de nuevo'], 'status' => 400];
        }
    }
}
