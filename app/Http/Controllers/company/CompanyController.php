<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\image\ImageController;
use App\Http\Controllers\utils\StorePhone;
use App\Http\Controllers\utils\StoreSocialMedia;
use App\Models\companies\Companies;

class CompanyController extends Controller {
    private $validateFields;
    
    public function __construct(){
        $this->validateFields = new ValidateFields();

        $required_role = serialize(['administrador general', 'superuser']);
        $required_module = "empresa";
        $this->middleware("auth:employee")->except(['index', 'show']);
        $this->middleware("roles:$required_role,$required_module")->except(['index', 'show']);
    }

    private function abortResponse(){
        return abort(response()->json(['message' => 'Forbidden'], 403));
    }

    // Query params -> page, search
    public function index(Request $req){
        $pagination = env('PAGINATION_PER_PAGE');
        $search = $req->query('search');
        $companies = Companies::where('estado', 1)->simplePaginate($pagination);

        if($search){
            $company = Companies::where('estado', 1)
            ->where('nombreempresa', 'LIKE', '%'.$search.'%')
            ->simplePaginate($pagination);
	    
            $companies = $company;
        }

        if(isset($companies)){
            $response = ['res' => ['data' => $companies], 'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los datos de las empresas, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function create(){
        //
    }

    public function store(Request $req){
        if($req->permissions['create']){
            $validator = $this->validateFields->validateWithPhone($req, [
                'company_name', 'nit', 'id_city', 'sm', 'sm_length', 'phone',
                'cellphone', 'cp_length', 'p_length', 'indicative', 'image'
            ], 1, 5);
            if($validator) return response($validator['res'], $validator['status']);

            $company = Companies::create([
                'idciudad' => $req->id_city,
                'nombreempresa' => $req->company_name,
                'nitempresa' => $req->nit,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if($company){
                $company->logo = ImageController::storeImage('company', $req->file('image'));
                $company->save();

                StorePhone::store($req, $company, 'empresa', 'cp', 'create');
                StorePhone::store($req, $company, 'empresa', 'p', 'create');
                StoreSocialMedia::store($req, $company, 'create');
            }

            if(isset($company)){
                $response = ['res' => ['message' => 'Los datos de la empresa fueron creados correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear los datos de la empresa, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);
            
        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        $company = Companies::where('estado', 1)->where('id', $id)->first();

        if($company){
            $city = $company->city()->first();
            $socialMedia = $company->socialMedia()->get();
            $phones = $company->phone()->get();
            $department = $city->department()->first();

            $company->idpais = $department->idpais;
            $company->iddepar = $city->iddepar;
            $company->redessociales = $socialMedia;
            $company->telefonos = $phones;
        }

        if(isset($company)){
            $response = ['res' => ['data' => $company], 'status' => 200];
        } else {
            $response = ['res' => ['message' => 'Hubo un error al obtener los datos de las empresas, intentalo de nuevo'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function edit(Request $req, $id){
       //
    }

    // _method: PUT required field
    public function update(Request $req, $id){
        if($req->permissions['update']){
            //PUT method, in future PATCH method could be implement for delete.
            if($req->isMethod('PUT')){
                $validator = $this->validateFields->validateWithPhone($req, [
                    'company_name', 'nit', 'id_city', 'sm', 'sm_length', 'phone',
                    'cellphone', 'cp_length', 'p_length', 'indicative', 'image'
                ], 1, 5);
                if($validator) return response($validator['res'], $validator['status']);

                $company = Companies::where('id', $id)->first();
                
                if($company){
                    $company->update([
                        'idciudad' => $req->id_city,
                        'nombreempresa' => $req->company_name,
                        'nitempresa' => $req->nit,
                        'fechamodificacion' => date('Y-m-d')
                    ]);
                    
                    if($req->file('logo')){
                        $company->logo = ImageController::updateImage('company', $company->logo, $req->file('image'), 'logo');
                    }
                    
                    $company->save();
    
                    StorePhone::store($req, $company, 'empresa', 'cp', 'update');
                    StorePhone::store($req, $company, 'empresa', 'p', 'update');
                    StoreSocialMedia::store($req, $company, 'update');
                }

                if(isset($company)){
                    $response = ['res' => ['message' => 'Los datos de la empresa fueron actualizados correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar los datos de la empresa, intentalo de nuevo'], 'status' => 400];
                }
        
                return response($response['res'], $response['status']);
            }

        } else {
            return $this->abortResponse();
        }
    }

    public function destroy($id){
        //
    }
}
