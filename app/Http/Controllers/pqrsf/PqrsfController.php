<?php

namespace App\Http\Controllers\pqrsf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\image\ImageController;
use App\Models\pqrsf\Pqrsf;
use App\Models\parameters\ParametersValues;

class PqrsfController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();

        $required_role = 'administrador de pqrsf';
        $required_module = "pqrsf";
        $this->middleware('auth:employee')->except(['store', 'myPqrsf']);   
        $this->middleware("roles:$required_role,$required_module")->except(['store', 'myPqrsf']);
    }

    private function abortResponse($msg){
        return abort(response()->json(['message' => $msg], 403));
    }

    private function getPqrsfStatus(){
        $pqrsfStatus = ParametersValues::where('nombretipos', 'abierto')->first();
        return $pqrsfStatus->id;
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $search = $req->query('search');
    
            $pqrsf = Pqrsf::where('estado', 1)->simplePaginate($pagination);
    
            if($search){
                $pqrsf = Pqrsf::where('estado', 1)->where('asunto', 'LIKE', '%'.$search.'%')->simplePaginate($pagination);
            }
    
            if(isset($pqrsf)){
                $response = ['res' => ['data' => $pqrsf],  'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener las pqrsf'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function myPqrsf(Request $req){
        $pagination = env('PAGINATION_PER_PAGE');
        $search = $req->query('search');
        $userId = null;

        if(Auth::guard('employee')->check()){
            $userId = Auth::guard('employee')->user()->idpersona;
        } else if(Auth::guard('customer')->check()){
            $userId = Auth::guard('customer')->user()->idpersona;
        } else {
            return $this->abortResponse('There was a problem with token validation');
        }

        $pqrsf = Pqrsf::where('estado', 1)
        ->where('idpersona', $userId)
        ->simplePaginate($pagination);

        if($search){
            $pqrsf = Pqrsf::where('estado', 1)
            ->where('idpersona', $userId)
            ->where('asunto', 'LIKE', '%'.$search.'%')->simplePaginate($pagination);
        }

        if(isset($pqrsf)){
            $response = ['res' => ['data' => $pqrsf],  'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener las pqrsf'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function create(){
        //
    }

    public function store(Request $req){
        $userId = null;
        $anonymous = $req->query('anonymous');
        $fields = ['pqrsf_type', 'subject', 'description', 'image'];
        
        if(Auth::guard('employee')->check()) {
            $userId = Auth::guard('employee')->user()->idpersona;
        } else if(Auth::guard('customer')->check()) {
            $userId = Auth::guard('customer')->user()->idpersona;
        } else if($anonymous == '1') {
            array_push($fields, 'first_name', 'last_name', 'email', 'id_number', 'id_type');
        } else {
            return $this->abortResponse('There was a problem with token validation or parameter anonymous is not defined');
        }

        $validator = $this->validateFields->validate($req, $fields);
        if($validator) return response($validator['res'], $validator['status']);

        if($userId){
            $pqrsf = Pqrsf::create([
                'idpersona' => $userId,
                'estadosolicitudpqrsf' => $this->getPqrsfStatus(),
                'tiposolicitudpqrsf' => $req->pqrsf_type,
                'asunto' => $req->subject,
                'descripcion' => $req->description,
                'imgayuda' => null,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);
        } else {
            $pqrsf = Pqrsf::create([
                'idpersona' => null,
                'estadosolicitudpqrsf' => $this->getPqrsfStatus(),
                'tiposolicitudpqrsf' => $req->pqrsf_type,
                'tipodocumento' => $req->id_type,
                'asunto' => $req->subject,
                'descripcion' => $req->description,
                'nombres' => $req->first_name,
                'apellidos' => $req->last_name,
                'email' => $req->email,
                'numerodocumento' => $req->id_number,
                'imgayuda' => null,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);    
        }

        if($pqrsf) {
            $pqrsf->imgayuda = ImageController::storeImage('pqrsf', $req->file('image'));
            $pqrsf->save();
        }

        if(isset($pqrsf)){
            $response = ['res' => ['message' => 'La pqrsf fue creada correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al crear la pqrsf, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function show($id){
        //
    }

    public function edit($id){
        //
    }

    public function update(Request $req, $id){
        if($req->permissions['update']){
            $validator = $this->validateFields->validate($req, ['pqrsf_status']);
            if($validator) return response($validator['res'], $validator['status']);

            $pqrsf = Pqrsf::where('id', $id)->update([
                'estadosolicitudpqrsf' => $req->pqrsf_status,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($pqrsf)){
                $response = ['res' => ['message' => 'Estado de la solicitud pqrsf correctamente'], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al actualizar la solicitud de la pqrsf, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse('Forbidden');
        }
    }

    public function destroy($id){
        //
    }
}
