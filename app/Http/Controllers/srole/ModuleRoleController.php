<?php

namespace App\Http\Controllers\srole;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\roles\ModulesRoles;
use App\Models\roles\UserModulesRoles;
use App\Models\roles\Roles;
use App\Models\roles\Modules;

class ModuleRoleController extends Controller {
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

            $modulesRoles = ModulesRoles::where('estado', 1)->get([
                'id',
                'idroles as rol',
                'idmodulos as modulo',
                'crear', 'actualizar',
                'leer',
                'eliminar',
                'fechacreacion',
                'fechamodificacion'
            ]);
            
            foreach($modulesRoles as $moduleRole){
                $role = Roles::where('id', $moduleRole->rol)->first();
                $module = Modules::where('id', $moduleRole->modulo)->first();
                $moduleRole->rol = $role->nombrerol;
                $moduleRole->modulo = $module->nombremodulo;
            }

            if($search){
                $modulesRoles = $modulesRoles->filter(function($item) use($search) {
                    return str_contains(strtolower($item->rol), strtolower($search)) ? $item->rol : null;
                });
            }

            $modulesRoles = $modulesRoles->paginate($pagination);

            if(isset($modulesRoles)){
                $response = ['res' => ['data' => $modulesRoles], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener los datos de acceso de los roles, intentalo de nuevo'], 'status' => 400];
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
            $validator = $this->validateFields->validate($req, ['role_id', 'module_id', 'create', 'update', 'read', 'delete']);
            if($validator) return response($validator['res'], $validator['status']);
            
            $moduleRole = ModulesRoles::create([
                'idroles' => $req->role_id,
                'idmodulos' => $req->module_id,
                'crear' => $req->create,
                'actualizar' => $req->update,
                'leer' => $req->read,
                'eliminar' => $req->delete,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($moduleRole)){
                $response = ['res' => ['message' => 'Los accesos fueron creados correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear los accesos, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $moduleRole = ModulesRoles::where('estado', 1)->where('id', $id)->first();

            if(isset($moduleRole)){
                $response = ['res' => ['data' => $moduleRole], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'No se pudo obtener los accesos. O la informacion de los accesos no existe, intentalo de nuevo'], 'status' => 400];
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
                $validator = $this->validateFields->validate($req, ['role_id', 'module_id', 'create', 'update', 'read', 'delete']);
                if($validator) return response($validator['res'], $validator['status']);

                $moduleRole = ModulesRoles::where('id', $id)->update([
                    'idroles' => $req->role_id,
                    'idmodulos' => $req->module_id,
                    'crear' => $req->create,
                    'actualizar' => $req->update,
                    'leer' => $req->read,
                    'eliminar' => $req->delete,
                    'fechamodificacion' => date('Y-m-d')
                ]);

                if(isset($moduleRole)){
                    $response = ['res' => ['message' => 'Los accesos fueron actualizados correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar los accesos, intentalo de nuevo'], 'status' => 400];
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
            $moduleRole = ModulesRoles::where('id', $id)->first();

            if($moduleRole){
                $moduleRole->update([
                    'estado' => 0,
                    'fechamodificacion' => date('Y-m-d')
                ]);

                UserModulesRoles::where('idmodrol', $moduleRole->id)->delete();
            }
    
            if($moduleRole){
                return $response = ['res' => ['message' => 'Los accesos fueron eliminados correctamente'], 'status' => 200];
            } else {
                return $response = ['res' => ['message' => 'Hubo un error al eliminar los accesos, intentalo de nuevo'], 'status' => 400];
            }

        } else {
            return $this->abortResponse();
        }
    }
}
