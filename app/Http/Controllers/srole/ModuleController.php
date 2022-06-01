<?php

namespace App\Http\Controllers\sRole;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\roles\Modules;

class ModuleController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();

        $required_role = serialize(['administrador de roles']);
        $required_module = "roles";
        $this->middleware("roles:$required_role,$required_module");
    }

    private function abortResponse(){
        return abort(response()->json(['message' => 'Forbidden'], 403));
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $search = $req->query('search');
            $modules = Modules::where('estado', 1)->simplePaginate($pagination);

            if($search){
                $modules = Modules::where('estado', 1)
                ->where('nombremodulo', 'LIKE', '%'.$search.'%')
                ->simplePaginate($pagination);
            }

            if(isset($modules)){
                $response = ['res' => ['data' => $modules], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener los modulos, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function create(){
        //
    }

    public function store(Request $req){
        if($req->permissions['create']){
            $validator = $this->validateFields->validate($req, ['module_name', 'module_desc']);
            if($validator) return response($validator['res'], $validator['status']);

            $module = Modules::create([
                'nombremodulo' => ucfirst(strtolower($req->module_name)),
                'descripcionmodulo' => $req->module_desc,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($module)){
                $response = ['res' => ['message' => 'El modulo fue creado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear el modulo, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $module = Modules::where('estado', 1)->where('id', $id)->first();

            if(isset($module)){
                $response = ['res' => ['data' => $module], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'No se pudo obtener el modulo. O el modulo no existe, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function edit($id){
        //
    }

    public function update(Request $req, $id){
        if($req->isMethod('PUT')){
            if($req->permissions['update']){
                $validator = $this->validateFields->validate($req, ['module_name', 'module_desc']);
                if($validator) return response($validator['res'], $validator['status']);
    
                $module = Modules::where('id', $id)->update([
                    'nombremodulo' => ucfirst(strtolower($req->module_name)),
                    'descripcionmodulo' => $req->module_desc,
                    'fechamodificacion' => date('Y-m-d')
                ]);

                if($module){
                    $response = ['res' => ['message' => 'El modulo fue actualizado correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar el modulo, intentalo de nuevo'], 'status' => 400];
                }
        
                return response($response['res'], $response['status']);
    
            } else {
                return $this->abortResponse();
            }
        } else if($req->isMethod('PATCH')){
            $response = $this->destroy($req, $id);

            return response($response['res'], $response['status']);
        }   
    }

    public function destroy($req, $id){
        if($req->permissions['delete']){
            $module = Modules::where('id', $id)->update([
                'estado' => 0,
                'fechamodificacion' => date('Y-m-d')
            ]);
    
            if($module){
                return $response = ['res' => ['message' => 'El modulo fue eliminado correctamente'], 'status' => 200];
            } else {
                return $response = ['res' => ['message' => 'Hubo un error al eliminar el modulo, intentalo de nuevo'], 'status' => 400];
            }
            
        } else {
            return $this->abortResponse();
        }
    }
}
