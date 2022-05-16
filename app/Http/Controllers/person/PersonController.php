<?php

namespace App\Http\Controllers\person;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\persons\Persons;
use App\Models\employees\Employees;
use App\Models\customers\Customers;
use App\Models\parameters\ParametersValues;
use App\Models\pqrsf\Pqrsf;
use App\Http\Controllers\image\ImageController;
use App\Http\Controllers\address\AddressController;
use App\Http\Controllers\utils\StorePhone;

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

    private function userQuery($table, $search){
        return DB::table($table)->where('estado', 1)
            ->where('nombreusuario', 'LIKE', '%'.$search.'%')
            ->get()
            ->map(function($user){
                unset($user->contrasena);
                unset($user->intentos);
                unset($user->estado);
                return $user;
            });
    }

    private function getUsers($req, $userType=null, $search=null){
        $pagination = env('PAGINATION_PER_PAGE');
        
        if($userType == 'employees'){
            $users = $this->userQuery('usuarios', $search);
        } else if($userType == 'customers'){
            $users = $this->userQuery('clientes', $search);
        } else {
            $query1 = $this->userQuery('usuarios', $search);
            $query2 = $this->userQuery('clientes', $search);     
            $users = $query2->merge($query1);
        }

        return collect($users)->paginate($pagination); 
    }

    private function setObservation($req, $person){
        return [
            'idpersona' => $person->id,
            'identradaproductos' => null,
            'observacion' => $req->observations, 
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
        ];
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $userType = $req->query('user_type');
            $search = $req->query('search');

            $users = $this->getUsers($req, $userType);

            if($search) return response (['data' => $this->getUsers($req, $userType, $search), 200]);

            if(isset($users)){
                $response = ['res' => ['data' => $users],  'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener los usuarios, intentalo de nuevo'], 'status' => 400];
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
            // Image deleted because admin is not who uploads employee's avatar
            $validator = $this->validateFields->validateWithPhone($req, [
                'id_type', 'id_city', 'id_gender', 'username', 'email', 
                'password', 'first_name', 'last_name', 'id_number', 'birthday',
                'id_address_type', 'address', 'postal_code', 'complements',
                'cellphone', 'phone', 'cp_length', 'p_length', 'indicative'
            ], 3, null, 'usuarios');
            if($validator) return response($validator['res'], $validator['status']);

            $user = Persons::create([
                'tipodocumento' => $req->id_type,
                'idciudad' => $req->id_city,
                'genero' => $req->id_gender,
                'nombres' => $req->first_name,
                'apellidos' => $req->last_name,
                'numerodocumento' => $req->id_number,
                'fechanacimiento' => date($req->birthday),
                'avatar' => null,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);
    
            if($user){
                // $user->avatar = ImageController::storeImage('avatars', $req->file('image'));
                // $user->save();
                
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
                
                StorePhone::store($req, $user, 'persona', 'cp', 'create');
                StorePhone::store($req, $user, 'persona', 'p', 'create');
            }
    
            if(isset($user)){
                $response = ['res' => ['message' => 'El usuario fue creado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear el usuario, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);
        } else {
            return $this->abortResponse();
        }
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
        if($req->permissions['read']){
            $person = Persons::where('estado', 1)->where('id', $id)->first();

            if($person){
                $address = $person->address()->get();
                $numbers = $person->phone()->get();

                $person->direccion = $address;
                $person->numeros = $numbers;
            }

            if(isset($person)){
                $response = ['res' => ['data' => $person], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'No se pudo obtener el usuario. O el usuario no existe, intentalo de nuevo'], 'status' => 400];
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
                $fields = ['id_type', 'id_gender', 'email', 'id_number', 'birthday', 'observations'];
    
                $person = Persons::where('id', $id)->first();
    
                if($person){
                    $employee = $person->employee()->where('idpersona', $id)->first();
                    $customer = $person->customer()->where('idpersona', $id)->first();
    
                    if($employee){
                        array_push($fields, 'id_city', 'first_name', 'last_name');
                        $userType = 'usuarios';
        
                    } else if($customer) $userType = 'clientes';
    
                    $validator = $this->validateFields->validate($req, $fields, $userType);
                    if($validator) return response($validator['res'], $validator['status']);  
        
                    if($person && $employee){
                        $person->update([
                            'tipodocumento' => $req->id_type,
                            'idciudad' => $req->id_city,
                            'genero' => $req->id_gender,
                            'nombres' => $req->first_name,
                            'apellidos' => $req->last_name,
                            'numerodocumento' => $req->id_number,
                            'fechanacimiento' => date($req->birthday),
                            'fechamodificacion' =>  date('Y-m-d')
                        ]);
        
                        $person->observation()->create($this->setObservation($req, $person));
                        $person->employee()->email = $req->email;
                        $person->save();
                        
                    } else if($person && $customer){
                        $person->update([
                            'tipodocumento' => $req->id_type,
                            'genero' => $req->id_gender,
                            'numerodocumento' => $req->id_number,
                            'fechanacimiento' => $req->birthday ? date($req->birthday) : null,
                            'fechamodificacion' =>  date('Y-m-d')
                        ]);
    
                        $person->observation()->create($this->setObservation($req, $person));
                        $person->customer()->email = $req->email;
                        $person->save();
                    }
                }
    
                if(isset($person)){
                    $response = ['res' => ['message' => 'Los datos del usuario fueron actualizados correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'No se pudo actualizar el usuario, intentalo de nuevo'], 'status' => 400];
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
            $person = Persons::where('id', $id)->first();

            if($person){
                $status = ['estado' => 0, 'fechamodificacion' => date('Y-m-d')];

                $person->update($status);
                $person->employee()->update($status);
                $person->address()->update(['estadodireccion' => 0, 'fechamodificacion' => date('Y-m-d')]);
                $person->phone()->update($status);
                $person->pqrsf()->update($status);
                $person->observation()->update($status);
            }

            if(isset($person)){
                return ['res' => ['message' => 'El usuario fue eliminado correctamente'], 'status' => 200];
            } else {
                return ['res' => ['message' => 'Hubo un error al eliminar el usuario, intentalo de nuevo'], 'status' => 400];
            }

        } else {
            return $this->abortResponse();
        }
    }
}
