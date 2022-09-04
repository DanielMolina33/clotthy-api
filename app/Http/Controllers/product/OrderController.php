<?php

namespace App\Http\Controllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\products\Orders;
use App\Models\parameters\ParametersValues;

class OrderController extends Controller {
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

    private function getOrderStatus(){
        $orderStatus = ParametersValues::where('nombretipos', 'pendiente')->first();
        return $orderStatus->id;
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $orders = Orders::where('estado', 1)->simplePaginate($pagination);

            if(isset($orders)){
                $response = ['res' => ['data' => $orders], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener las ordenes, intentalo de nuevo'], 'status' => 400];
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
                'id_supplier', 'applicant', 'order_desc', 'prod_name', 'prod_amount'
            ]);
            if($validator) return response($validator['res'], $validator['status']);

            $order = Orders::create([
                'idproveedor' => $req->id_supplier,
                'estadoorden' => $this->getOrderStatus(),
                'emisororden' => $req->applicant,
                'descripcionorden' => $req->order_desc,
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if($order){
                foreach($req->prod_names as $key => $prodName){
                    $prodAmount = $req->prod_amounts[$key];
                    $order->product()->create([
                        'idorden' => $order->id,
                        'nombreproducto' => $prodName,
                        'cantidad' => $prodAmount,
                    ]);
                }
            }

            if(isset($order)){
                $response = ['res' => ['message' => 'La orden fue creada correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear la orden, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);
            
        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $order = Orders::where('estado', 1)->where('id', $id)->first();

            if($order){
                $products = $order->product()->get();
                $order->productos = $products;
            }

            if(isset($order)){
                $response = ['res' => ['data' => $order], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la orden, intentalo de nuevo'], 'status' => 400];
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
        if($req->permissions['update']){
            $validator = $this->validateFields->validate($req, ['order_status', 'order_desc']);
            if($validator) return response($validator['res'], $validator['status']);

            $order = Orders::where('id', $id)->update([
                'estadoorden' => $req->order_status,
                'descripcionorden' => $req->order_desc,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($order)){
                $response = ['res' => ['message' => 'La orden fue actualizada correctamente'], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al actualizar la orden, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);
    
        } else {
            return $this->abortResponse();
        }
    }

    public function destroy($id){
        //
    }
}

