<?php

namespace App\Http\Controllers\person;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\persons\Persons;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\roles\UserRoles;
use App\Models\employees\Employees;
use App\Http\Controllers\image\ImageController;

class PersonController extends Controller {
    private $validateFields;

    function __construct(){
        $this->validateFields = new ValidateFields();

        $required_role = 'administrador de usuarios';
        $required_module = "usuarios";
        $this->middleware("roles:$required_role,$required_module");
    }

    private function abortResponse(){
        return abort(response()->json(['message' => 'Forbidden'], 403));
    }

    public function index(Request $req){
        //
    }

    public function create(){
        //
    }

    public function store(Request $req){
        $validator = $this->validateFields->validate($req, [
            'id_type', 'id_city', 'id_gender', 'username', 'email', 
            'password', 'first_name', 'last_name', 'id_number', 'birthday', 'avatar'
        ], 'usuarios');
        if($validator) return response($validator['res'], $validator['status']);

        $user = Persons::create([
            'tipodocumento' => $req->id_type,
            'idciudad' => $req->id_city,
            'genero' => $req->id_gender,
            'nombres' => $req->first_name,
            'apellidos' => $req->last_name,
            'numerodocumento' => $req->id_number,
            'fechanacimiento' => date($req->birthday),
            'avatar' => ImageController::storeImage($req->file('avatar')),
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        if($user){
            $employee = Employees::create([
                'nombreusuario' => $req->username,
                'idPersona' => $user->id,
                'email' => $req->email,
                'contrasena' => Hash::make($req->password),
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);
        }

        if(isset($user) && isset($employee)){
            $response = ['res' => ['message' => 'El usuario fue creado correctamente'], 'status' => 201];
        } else {
            $response = ['res' => ['message' => 'Hubo un problema al crear el usuario, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public static function storeEmpty(){
        $person = Persons::create([
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);

        return $person->id;
    }

    public function show(Request $req, $id){
        if($req->permissions['create']){
            $person = Persons::firstWhere('id', $id);
            if($person){
                return response(['user' => $person], 200);
            } else {
                return response(['user' => $person]);
            }
        } else {
            return $this->abortResponse();
        }
    }

    public function edit($id){
        //
    }

    public function update(Request $request, $id){
        //
    }

    public function destroy($id){
        //
    }
}
