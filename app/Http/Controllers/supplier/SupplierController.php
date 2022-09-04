<?php

namespace App\Http\Controllers\supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\utils\StorePhone;
use App\Models\suppliers\Suppliers;

class SupplierController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();

        $required_role = serialize(['administrador de productos', 'superuser']);
        $required_module = "productos";
        $this->middleware("roles:$required_role,$required_module");
    }

    private function abortResponse(){
        return abort(response()->json(['message' => 'Forbidden'], 403));
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $search = $req->query('search');

            $suppliers = Suppliers::where('estado', 1)->simplePaginate($pagination);

            if($search) {
                $suppliers = Suppliers::where('estado', 1)
                ->where('nombreproveedor', 'LIKE', '%'.$search.'%')
                ->simplePaginate($pagination);
            }

            if(isset($suppliers)){
                $response = ['res' => ['data' => $suppliers],  'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener los proveedores, intentalo de nuevo'], 'status' => 400];
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
            $validator = $this->validateFields->validateWithPhone($req, [
                'id_type', 'id_city', 'supplier_name', 'id_number', 'supplier_desc', 
                'supplier_email', 'agent', 'id_address_type', 'address', 'postal_code', 'complements',
                'cellphone', 'phone', 'cp_length', 'p_length', 'indicative'
            ], 2, null);
            if($validator) return response($validator['res'], $validator['status']);

            $supplier = Suppliers::create([
                'tipodocumento' => $req->id_type,
                'idciudades' => $req->id_city,
                'nombreproveedor' => $req->supplier_name,
                'numerodocumento' => $req->id_number,
                'descripcion' => $req->supplier_desc,
                'correoproveedor' => $req->supplier_email,
                'representante' => $req->agent,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if($supplier){
                $supplier->address()->create([
                    'tipodireccion' => $req->id_address_type,
                    'idpersona' => null,
                    'idproveedor' => $supplier->id,
                    'direccion' => $req->address,
                    'codigopostal' => $req->postal_code,
                    'complementos' => $req->complements,
                    'estadodireccion' => 1,
                    'fechacreacion' => date('Y-m-d'),
                    'fechamodificacion' => date('Y-m-d')
                ]);

                StorePhone::store($req, $supplier, 'proveedor', 'cp', 'create');
                StorePhone::store($req, $supplier, 'proveedor', 'p', 'create');
            }

            if(isset($supplier)){
                $response = ['res' => ['message' => 'El proveedor fue creado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear el proveedor, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $supplier = Suppliers::where('estado', 1)->where('id', $id)->first();

            if($supplier){
                $numbers = $supplier->phone()->get();
                $address = $supplier->address()->get();

                $supplier->numeros = $numbers;
                $supplier->direcciones = $address;
            }

            if(isset($supplier)){
                $response = ['res' => ['data' => $supplier], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener el proveedor, intentalo de nuevo'], 'status' => 400];
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
                $validator = $this->validateFields->validateWithPhone($req, [
                    'id_type', 'id_city', 'supplier_name', 'id_number', 'supplier_desc', 
                    'supplier_email', 'agent', 'id_address_type', 'address', 'postal_code', 'complements',
                    'cellphone', 'phone', 'cp_length', 'p_length', 'indicative'
                ], 2, null);
                if($validator) return response($validator['res'], $validator['status']);
    
                $supplier = Suppliers::where('id', $id)->first();
    
                if($supplier){
                    $supplier->update([
                        'tipodocumento' => $req->id_type,
                        'idciudades' => $req->id_city,
                        'nombreproveedor' => $req->supplier_name,
                        'numerodocumento' => $req->id_number,
                        'descripcion' => $req->supplier_desc,
                        'correoproveedor' => $req->supplier_email,
                        'representante' => $req->agent,
                        'fechamodificacion' => date('Y-m-d')
                    ]);
    
                    $supplier->address()->update([
                        'tipodireccion' => $req->id_address_type,
                        'idpersona' => null,
                        'idproveedor' => $supplier->id,
                        'direccion' => $req->address,
                        'codigopostal' => $req->postal_code,
                        'complementos' => $req->complements,
                        'fechamodificacion' => date('Y-m-d')
                    ]);
    
                    StorePhone::store($req, $supplier, 'proveedor', 'cp', 'update');
                    StorePhone::store($req, $supplier, 'proveedor', 'p', 'update');

                    $supplier->save();
                }
    
    
                if(isset($supplier)){
                    $response = ['res' => ['message' => 'Los datos del proveedor fueron actualizados correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar los datos del proveedor'], 'status' => 400];
                }
        
                return response($response['res'], $response['status']);
    
            } else {
                return $this->abortResponse();
            }
        } else {
            $response = $this->destroy($req, $id);

            return response($response['res'], $response['status']);
        }
    }

    public function destroy(Request $req, $id){
        if($req->permissions['delete']){
            $supplier = Suppliers::where('id', $id)->first();

            if($supplier){
                $status = ['estado' => 0, 'fechamodificacion' => date('Y-m-d')];

                $supplier->update($status);
                $supplier->address()->update(['estadodireccion' => 0, 'fechamodificacion' => date('Y-m-d')]);
                $supplier->phone()->update($status);
            }

            if(isset($supplier)){
                return ['res' => ['message' => 'El proveedor fue eliminado correctamente'], 'status' => 200];
            } else {
                return ['res' => ['message' => 'Hubo un error al eliminar el proveedor, intentalo de nuevo'], 'status' => 400];
            }

        } else {
            return $this->abortResponse();
        }
    }
}
