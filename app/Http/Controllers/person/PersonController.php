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
use App\Http\Controllers\address\AddressController;

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

    private function storePhone($data){
        $user->phone()->create([
            'tiponumero' => $data['id_number_type'],
            'idproveedor' => null,
            'idempresa' => null,
            'idusuario' => $data['id'],
            'numerotelefono' => $data['number'],
            'idicativo' => $data['indicative'],
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ]);
    }

    private function getNumbersData($req, $name){
        $numbersData = [];
        $field = $name.'_length';
        $length = intval($req->$field);
        for($i = 1; $i <= $length; $i++){
            $numberField = $name.'_'.$i;
            $number = $req->$numberField;
            array_push($numbersData, $number);
        }

        return $numbersData;
    }

    public function index(Request $req){
        //
    }

    public function create(){
        //
    }

    public function store(Request $req){
        // $cellphonesData = $this->getNumbersData($req, 'cellphone');
        // $phonesData = $this->getNumbersData($req, 'phone');
        // if(is_numeric($req->cellphone_length) && strlen($req->cellphone_length) == 1){   

        $validator = $this->validateFields->validate($req, [
            'id_type', 'id_city', 'id_gender', 'username', 'email', 
            'password', 'first_name', 'last_name', 'id_number', 'birthday', 'avatar',
            'id_address_type', 'address', 'postal_code', 'complements',
            'cellphone', 'phone', 'cellphone_length', 'phone_length'
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
            $user->employee()->create([
                'nombreusuario' => $req->username,
                'idpersona' => $user->id,
                'email' => $req->email,
                'contrasena' => Hash::make($req->password),
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            $user->address()->create([
                'tipodireccion' => $req->id_address_type,
                'idpersona' => $user->id,
                'idproveedor' => null,
                'direccion' => $req->address,
                'codigopostal' => $req->postal_code,
                'complementos' => $req->complements,
                'estadodireccion' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            
            // $this->storePhone([
            //     'tiponumero' => $data['id_number_type'],
            //     'idusuario' => $data['id'],
            //     'numerotelefono' => $data['number'],
            //     'idicativo' => $data['indicative'],
            // ]);
        }

        if(isset($user)){
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
