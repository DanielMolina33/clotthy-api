<?php

namespace App\Http\Controllers\sRole;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\roles\Roles;

class sRoleController extends Controller {
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
            $roles = Roles::where('estado', 1)->simplePaginate($pagination);

            if($search){
                $roles = Roles::where('estado', 1)
                ->where('nombrerol', 'LIKE', '%'.$search.'%')
                ->simplePaginate($pagination);
            }

            if(isset($roles)){
                $response = ['res' => ['data' => $roles], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener los roles, intentalo de nuevo'], 'status' => 400];
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
            $validator = $this->validateFields->validate($req, ['role_name', 'role_desc']);
            if($validator) return response($validator['res'], $validator['status']);

            $role = Roles::create([
                'nombrerol' => ucfirst(strtolower($req->role_name)),    
                'descripcionrol' => $req->role_desc,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($role)){
                $response = ['res' => ['message' => 'El rol fue creado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear el rol, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $role = Roles::where('estado', 1)->where('id', $id)->first();
            $role->moduleRole[0]->pivot;

            if(isset($role)){
                $response = ['res' => ['data' => $role], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'No se pudo obtener el rol. O el rol no existe, intentalo de nuevo'], 'status' => 400];
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
                $validator = $this->validateFields->validate($req, ['role_name', 'role_desc']);
                if($validator) return response($validator['res'], $validator['status']);
    
                $role = Roles::where('id', $id)->update([
                    'nombrerol' => ucfirst(strtolower($req->role_name)),  
                    'descripcionrol' => $req->role_desc,
                    'fechamodificacion' => date('Y-m-d')
                ]);
                
                if($role){
                    $response = ['res' => ['message' => 'El rol fue actualizado correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar el rol, intentalo de nuevo'], 'status' => 400];
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
            $role = Roles::where('id', $id)->update([
                'estado' => 0,
                'fechamodificacion' => date('Y-m-d')
            ]);
    
            if($role){
                return $response = ['res' => ['message' => 'El rol fue eliminado correctamente'], 'status' => 200];
            } else {
                return $response = ['res' => ['message' => 'Hubo un error al eliminar el rol, intentalo de nuevo'], 'status' => 400];
            }

        } else {
            return $this->abortResponse();
        }
    }
}
