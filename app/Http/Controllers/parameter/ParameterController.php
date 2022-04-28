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
        //
    }

    public function create(){
        //
    }

    public function store(Request $req){
        $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc']);
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
        $validator = $this->validateFields->validate($req, ['parameter_name', 'parameter_desc']);
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
    }

    public function destroy($id){
        //
    }
}
