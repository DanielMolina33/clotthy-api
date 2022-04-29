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
        $parameter = Parameters::where('estado','=',1)->get()->map(function($item){
            return [
                'parameter_name' => $item->nombretipo,
                'parameter_desc' => $item->descripciontipo,
                'created_at' => $item->fechacreacion,
                'updated_at' => $item->fechamodificacion,
            ];
        });

        if(isset($parameter)){
            $response = ['res' => ['data' => $parameter],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un problema al obtener los parametros'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function create(){
        //
    }

    public function store(Request $req){
        if(strlen($req->parameter_desc) > 0){
            $fields = ['parameter_name', 'parameter_desc'];
        } else {
            $fields = ['parameter_name'];
        }

        $validator = $this->validateFields->validate($req, $fields);
        if($validator) return response($validator['res'], $validator['status']);

        $parameter = Parameters::create([
            'nombretipo'=> $req->parameter_name,
            'descripciontipo'=> $req->parameter_desc,
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        if(isset($parameter)){
            $response = ['res' => ['message' => 'El parametro fue creado correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un problema al crear el parametro, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function show(Request $req, $id){
        //
    }

    public function edit($id){
        //
    }

    public function update(Request $req, $id){
        if ($req->isMethod('PUT')){
            if(strlen($req->parameter_desc) > 0){
                $fields = ['parameter_name', 'parameter_desc'];
            } else {
                $fields = ['parameter_name'];
            }

            $validator = $this->validateFields->validate($req, $fields);
            if($validator) return response($validator['res'], $validator['status']);

            $parameter = Parameters::where('id', $id)->update([
                'nombretipo'=> $req->parameter_name,
                'descripciontipo'=> $req->parameter_desc,
                'estado' => 1,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($parameter)){
                $response = ['res' => ['message' => 'El parametro fue actualizado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un problema al actualizar el parametro, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);
        }else if ($req->isMethod('PATCH')){
            $response = $this->destroy($req, $id);

            return response($response['res'], $response['status']);
        }

    }

    public function destroy(Request $req, $id){
        $parameter = Parameters::where('id', $id)->update([
            'estado' => 0,
        ]);

        if(isset($parameter)){
            return ['res' => ['message' => 'El parametro fue eliminado correctamente'], 'status' => 200];
        } else {
            return ['res' => ['message' => 'Hubo un problema al eliminar el parametro, intentalo de nuevo'], 'status' => 400];
        }
    }
}
