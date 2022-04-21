<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class getUserRolePermissions {
    public function handle(Request $request, Closure $next) {
        $userPermissions = DB::table("usuarios")
        ->join('usuario_modulo_rol', 'usuarios.id', '=', 'usuario_modulo_rol.idusuario')
        ->join('modulos_roles', 'modulos_roles.id', '=', 'usuario_modulo_rol.idmodrol')
        ->join('modulos', 'modulos.id', '=', 'modulos_roles.idModulos')
        ->join('roles', 'roles.id', '=', 'modulos_roles.idRoles')
        ->where('usuarios.id', '=', $userId)
        ->select('usuarios.nombreusuario', 'roles.nombrerol', 'modulos.nombremodulo', 
        'modulos_roles.crear', 'modulos_roles.leer', 'modulos_roles.actualizar', 'modulos_roles.eliminar')
        ->get();

        $request->merge(['userPermissions' => $userPermissions]);
        
        return $next($request);
    }
}
