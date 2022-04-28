<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\roles\UserRoles;

class Roles {
    public function handle(Request $req, Closure $next, $required_role, $required_module){
        $userRoles = new UserRoles($req, $required_role, $required_module);
        if($userRoles->roles['has_access']){
            $req->merge(['permissions' => $userRoles->roles['permissions']]);
        }

        return $next($req);
    }
}
