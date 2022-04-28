<?php

namespace App\Http\Controllers\roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoles extends Controller {
    public $roles;

    public function __construct($req, $role, $module){
        $id = $req->user()->only('id');
        $this->roles = $this->getRoles($id, $role, $module);
    }

    private function getRoles($id, $role, $module){
        $roles = DB::table("usuarios")
        ->join('usuario_modulo_rol', 'usuarios.id', '=', 'usuario_modulo_rol.idusuario')
        ->join('modulos_roles', 'modulos_roles.id', '=', 'usuario_modulo_rol.idmodrol')
        ->join('modulos', 'modulos.id', '=', 'modulos_roles.idModulos')
        ->join('roles', 'roles.id', '=', 'modulos_roles.idRoles')
        ->where('usuarios.id', '=', $id)
        ->where('roles.nombrerol', '=', $role)
        ->where('modulos.nombremodulo', '=', $module)
        ->select('roles.nombrerol', 'modulos.nombremodulo', 
        'modulos_roles.crear', 'modulos_roles.leer', 'modulos_roles.actualizar', 'modulos_roles.eliminar')
        ->get();

        $has_access = $this->checkModuleAccess($roles, $module);
        $permissions = $this->getPermissions($roles[0]);
        return ['permissions' => $permissions, 'has_access' => $has_access];
    }

    private function getPermissions($roles){
        $permissions = [];
        $permissions['create'] = $roles->crear;
        $permissions['read'] = $roles->leer;
        $permissions['update'] = $roles->actualizar;
        $permissions['delete'] = $roles->eliminar;
        return $permissions;
    }

    private function checkModuleAccess($roles, $module){
        if(count($roles) == 0 || strtolower($roles[0]->nombremodulo) !== strtolower($module)){
            return abort(response()->json(['message' => 'Forbidden'], 403));
        }

        return true;
    }
}
