<?php

namespace App\Http\Controllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Http\Controllers\image\ImageController;
use App\Http\Controllers\utils\Observations;
use App\Models\products\Inventory;

class InventoryController extends Controller {
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
            $productId = $req->query('product_id');

            if($productId){
                $inventory = Inventory::where('estado', 1)->where('idprod', $productId)->simplePaginate($pagination);
            } else {
                return response(['message' => 'product_id in url is required'], 400);
            }

            if(isset($inventory)){
                $response = ['res' => ['data' => $inventory], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion del inventario, intentalo de nuevo'], 'status' => 400];
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
            $validator = $this->validateFields->validate($req, [
                'id_prod', 'id_order', 'prod_amount', 'invoice', 
                'purchase_price', 'profit_percent', 'observations',
            ]);
            if($validator) return response($validator['res'], $validator['status']);

            $inventory = Inventory::create([
                'idprod' => $req->id_prod,
                'idorden' => $req->id_order,
                'cantidad' => $req->prod_amount,
                'precioproveedor' => $req->purchase_price,
                'porcentajeganancia' => $req->profit_percent,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if($inventory){
                $inventory->observation()->create(Observations::setObservation($req, null, $inventory->id));
                $inventory->urlfactura = ImageController::storeImage("products/$req->id_prod/invoices", $req->file('invoice'));
                $inventory->save();
            }

            if(isset($inventory)){
                $response = ['res' => ['message' => 'El inventario fue registrado correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al registrar el inventario, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);
            
        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $inventory = Inventory::where('estado', 1)->where('id', $id)->first();

            if($inventory){
                $observations = $inventory->observation()->get();
                $inventory->observacion = $observations;
            }

            if(isset($inventory)){
                $response = ['res' => ['data' => $inventory], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion del inventario, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse();
        }
    }

    public function edit($id){
        //
    }

    // CHECK, IF THERE ARE ANY SALE, THEN, THIS FUNCTION COULD NOT BE EXECUTED.
    // _method: PUT required field
    public function update(Request $req, $id){
        if($req->permissions['update']){
            //PUT method, in future PATCH method could be implement for delete.
            if($req->isMethod('PUT')){
                $validator = $this->validateFields->validate($req, [
                    'id_order', 'prod_amount', 'invoice', 
                    'purchase_price', 'profit_percent', 'observations',
                ]);
                if($validator) return response($validator['res'], $validator['status']);

                $inventory = Inventory::where('id', $id)->first();

                if($inventory){
                    $inventory->update([
                        'idorden' => $req->id_order,
                        'cantidad' => $req->prod_amount,
                        'precioproveedor' => $req->purchase_price,
                        'porcentajeganancia' => $req->profit_percent,
                        'fechamodificacion' => date('Y-m-d')
                    ]);

                    $inventory->urlfactura = ImageController::updateImage("products/$inventory->idprod/invoices", $inventory->invoice, $req->file('invoice'));
                    $inventory->save();
                }

                if(isset($inventory)){
                    $response = ['res' => ['message' => 'El inventario fue actualizado correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar la informacion del inventario, intentalo de nuevo'], 'status' => 400];
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
