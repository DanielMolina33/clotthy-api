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

    // Query params -> page, search->value = categorias
    public function index(Request $req){
        $pagination = env('PAGINATION_PER_PAGE');
        $search = $req->query('search');
        $parameter = Parameters::where('estado', 1)->simplePaginate($pagination);

        if($search){
            if($search == 'categorias'){
                $parameter = Parameters::where('escategoria', 1)->get();
            } else {
                $parameter = Parameters::where('nombretipo', 'LIKE', '%'.$search.'%')->first();
            }
        }

        if(isset($parameter)){
            $response = ['res' => ['data' => $parameter],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los parametros'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function store(Request $req){
        $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc', 'is_category']);
        if($validator) return response($validator['res'], $validator['status']);

        $parameter = Parameters::create([
            'nombretipo'=> ucfirst(strtolower($req->parameter_name)),
            'descripciontipo'=> $req->parameter_desc,
            'escategoria' => $req->is_category,
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
        if($req->isMethod('PUT')){
            $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc', 'is_category']);
            if($validator) return response($validator['res'], $validator['status']);

            $parameter = Parameters::where('id', $id)->update([
                'nombretipo'=> ucfirst(strtolower($req->parameter_name)),
                'descripciontipo'=> $req->parameter_desc,
                'escategoria' => $req->is_category,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($parameter)){
                $response = ['res' => ['message' => 'El parametro fue actualizado correctamente'], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al actualizar el parametro, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);
        } else if ($req->isMethod('PATCH')){
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
