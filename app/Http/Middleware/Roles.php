<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\roles\UserRoles;

class Roles {
    public function handle(Request $req, Closure $next, $required_roles, $required_module){
        $required_roles = unserialize($required_roles);
        $userRoles = new UserRoles($req, $required_roles, $required_module);
        if($userRoles->roles['has_access']){
            $req->merge(['permissions' => $userRoles->roles['permissions']]);
        }

        return $next($req);
    }
}
