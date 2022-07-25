<?php

namespace App\Http\Controllers\login;

use Laravel\Passport\Token;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\employees\Employees;
use App\Models\customers\Customers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\email\Emails;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\person\PersonController;

class LoginController extends Controller {
    private $emails;
    private $validateFields;

    public function __construct(){
        $this->emails = new Emails();
        $this->validateFields = new ValidateFields();
    }

    function register(Request $req){
        $validator = $this->validateFields->validate($req, ['username', 'email', 'password'], 'clientes');
        if($validator) return response($validator['res'], $validator['status']);

        $user = Customers::create([
            'nombreusuario' => $req->username,
            'idPersona' => PersonController::storeEmpty(),
            'email' => $req->email,
            'contrasena' => Hash::make($req->password),
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        if(isset($user)){
            $response = ['res' => ['message' => 'Tu usuario fue creado correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al crear tu usuario, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    function signIn(Request $req){
        $userType = $req->route('userType');

        $validator = $this->validateFields->validate($req, ['email', 'password']);
        if($validator) return response($validator['res'], $validator['status']);

        if($userType == 'employees'){
            $employee = Employees::where('email', $req->email)->first();
            $response = $this->verifyUser($req, $employee, 'employees');
            if(isset($response['res']['user'])){
                $employeePermissions = $this->getEmployeeRolePermissions($response['res']['user']['id']);
                if(count($employeePermissions) > 0){
                    $response['res']['user']['user_role_info'] = $employeePermissions;
                }
            }
        } else if($userType == 'customers'){
            $customer = Customers::where('email', $req->email)->first();
            $response = $this->verifyUser($req, $customer, 'customers');
        }
    
        return response($response['res'], $response['status']);
    }

    private function verifyUser($req, $user, $userType){
        if(isset($user)){
            if(Hash::check($req->password, $user->contrasena)){
                $attemps = $this->signInAttemps($user, $userType, true);
                
                if($user->intentos >= 1 && $user->intentos <= 3){
                    $token = $user->createToken('User logged')->accessToken;
                    $person = $user->person()->first();
                }

                return $attemps ? $attemps : [
                    'res' => [
                        'user' => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'username' => $user->nombreusuario,
                            'first_name' => $person->nombres,
                            'last_name' => $person->apellidos,
                            'avatar' => $person->avatar
                        ],
                        'token' => $token,
                    ], 
                    'status' => 200
                ];
            } else {
                $attemps = $this->signInAttemps($user, $userType, false);
                return $attemps ? $attemps : ['res' => ['message' => 'La contraseña es incorrecta'], 'status' => 400];
            }
        } else {
            return ['res' => ['message' => 'No se pudo encontrar el usuario'], 'status' => 400];
        }
    }

    private function signInAttemps($user, $userType, $signInSuccess){
        if($user->intentos >= 0){
            if(!$signInSuccess || $user->intentos == 0){
                if($user->intentos >= 1 && $user->intentos <= 3){
                    $user->intentos -= 1;
                    $user->save();
                } else {
                    $user->intentos = -1; 
                    $user->save();
                    $this->emailAttemps($user);
                    $emailResponse = $this->sendResetLink($user, $userType);
                    return [
                        'res' => [
                            'message' => 'Has superado el maximo de intentos para iniciar sesion, por favor cambia tu contraseña',
                            'email' => $emailResponse
                        ],
                        'status' => 400
                    ];
                }
            } else {
                if($user->intentos !== 3){
                    $user->intentos = 3;
                    $user->save();
                }
            }
        } else {    
            return [
                'res' => [
                    'message' => 'Has superado el maximo de intentos para iniciar sesion, por favor cambia tu contraseña'
                ],
                'status' => 400
            ];
        }
    }

    function forgotPassword(Request $req){
        $userType = $req->route('userType');

        $validator = $this->validateFields->validate($req, ['email']);
        if ($validator) return response($validator['res'], $validator['status']);

        if($userType == 'employees'){
            $user = Employees::where('email', $req->email)->first();
        } else if($userType == 'customers') {
            $user = Customers::where('email', $req->email)->first();
        }

        if(isset($user)){
            $response = $this->sendResetLink($user, $userType);
        } else {
            $response = ['res' => ['message' => 'No se pudo encontrar el usuario'], 'status' => 400];
        }
        
        return response($response['res'], $response['status']);
    }
    

    function passwordReset(Request $req){
        $validator = $this->validateFields->validate($req, ['password']);
        if ($validator) return response($validator['res'], $validator['status']);

        if($req->route('userType') == 'employees'){
            if(!Auth::guard('employee')->check()){
                return response(['message'=>'There was a problem with token validation'], 500); // Hacer nuevamente el proceso
            }

            $user = Employees::where('id', $req->route('id'))->first();

        } else if($req->route('userType') == 'customers'){
            if(!Auth::guard('customer')->check()){
                return response(['message'=>'There was a problem with token validation'], 500); // Hacer nuevamente el proceso
            }

            $user = Customers::where('id', $req->route('id'))->first();
        }

        if(isset($user)){
            if($user->intentos < 3) $user->intentos = 3;
            
            $user->contrasena = Hash::make($req->password);
            $user->fechamodificacion = date('Y-m-d');
            $user->save();

            $response = ['res' => ['message' => 'Contraseña actualizada correctamente'], 'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al actualizar tu contraseña, intentalo de nuevo'], 'status' => 400];
        }
        
        Token::where("id", $req->route('tokenId'))->delete();
        return response($response['res'], $response['status']);
    }

    private function sendResetLink($user, $userType){
        // check error key and action
        $tokenObj = $user->createToken('reset password token');
        $token = $tokenObj->accessToken;
        $tokenId = $tokenObj->token->id;
        return $this->emailResetPassword([
            'user' => $user,
            'userType' => $userType,
            'token' => $token,
            'tokenId' => $tokenId
        ]);
    }

    private function emailAttemps($user){
        return $this->emails->send(
            $user->email,
            'Advertencia, muchos intentos de acceso',
            'emails.mail',
            ['name' => $user->nombreusuario, 'body' => 'Hubo muchos intentos de ingresar a tu cuenta, en minutos te enviaremos un enlace para realizar el proceso'],
        );
    }

    private function emailResetPassword($data){
        return $this->emails->send(
            $data['user']->email,
            'Solicitud cambio de contraseña',
            'emails.mail',
            [
                'name' => $data['user']->nombreusuario, 
                'body' => 'Aqui está el enlace para cambiar tu contraseña: '.env('HOST_PASSWORD').'/password/reset/'.$data['userType'].'/'.$data['user']->id.'/'.$data['token'].'/'.$data['tokenId']
            ],
        );
    }

    private function getEmployeeRolePermissions($employeeId){
        $employeePermissions = DB::table("usuarios")
        ->join('usuario_modulo_rol', 'usuarios.id', '=', 'usuario_modulo_rol.idusuario')
        ->join('modulos_roles', 'modulos_roles.id', '=', 'usuario_modulo_rol.idmodrol')
        ->join('modulos', 'modulos.id', '=', 'modulos_roles.idModulos')
        ->join('roles', 'roles.id', '=', 'modulos_roles.idRoles')
        ->where('usuarios.id', '=', $employeeId)
        ->select('roles.nombrerol', 'modulos.nombremodulo', 
        'modulos_roles.crear', 'modulos_roles.leer', 'modulos_roles.actualizar', 'modulos_roles.eliminar')
        ->get()
        ->map(function($item){
                $permissions = [
                    'role' => $item->nombrerol,
                    'module' => $item->nombremodulo,
                    'permissions' => [
                        'create' => $item->crear,
                        'read' => $item->leer,
                        'update' => $item->actualizar,
                        'delete' => $item->eliminar
                    ]
                ];

                return $permissions;
        });

        return $employeePermissions;
    }
}