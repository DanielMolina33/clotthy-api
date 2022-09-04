<?php

namespace App\Http\Controllers\sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Illuminate\Support\Facades\Log;
use App\Models\sales\Sales;
use App\Models\customers\Customers;
use App\Models\persons\Persons;
use App\Models\cart\Cart;
use App\Http\Controllers\cart\CartController;
use App\Http\Controllers\validations\ValidateFields;

class SaleController extends Controller {
    private $cents;
    private $validateFields;

    public function __construct(){
        $this->cents = 100;
        $this->validateFields = new ValidateFields();
        $required_role = serialize(['administrador de productos', 'superuser']);
        $required_module = "productos";
        
        $this->middleware('auth:employee')->except(['getCartItems', 'store']);
        $this->middleware("roles:$required_role,$required_module")->except(['getCartItems', 'store']);
    }

    private function abortResponse($msg){
        return abort(response()->json(['message' => $msg], 403));
    }

    private function getCustomer($email, $idNumber){
        $customer = Customers::where('email', $email)->first();
        
        if(!$customer){
            $person = Persons::where('estado', 1)->where('numerodocumento', $idNumber)->first();
            $customer = $person->customer()->where('idpersona', $person->id)->first();
        }

        return $customer ? $customer->id : null;
    }

    public function index(Request $req){
        if($req->permissions['read']){
            $pagination = env('PAGINATION_PER_PAGE');
            $sales = Sales::simplePaginate($pagination);

            if(isset($sales)){
                $response = ['res' => ['data' => $sales], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion de las ventas, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);

        } else {
            $this->abortResponse("Forbidden");
        }
    }

    public function getCartItems(Request $req){
        if(Auth::guard('customer')->check()){
            $saleId = $req->query("sale_id");
            $customerId = Auth::guard('customer')->user()->id;
            $cart = Cart::where('estado', 1)->where('idcliente', $customerId)->first();
            
            if($cart){
                if($saleId){
                    $productsCart = $cart->productCart()->get()->toArray();
                    $sale = Sales::where('id', $saleId)->first();
    
                    if($sale){
                        $sale->update([
                            'idcliente' => $customerId,
                            'fechacreacion' => date('Y-m-d'),
                            'fechamodificacion' => date('Y-m-d')
                        ]);
    
                        foreach($productsCart as $key => $productCart){
                            $sale->productSale()->attach($productCart['id'], [
                                'idventa' => $saleId,
                                'idprod' => $productCart['id'],
                                'cantidad' => $productCart['pivot']['cantidadproductos'],
                            ]);
                        }
    
                        $sale->save();
                        $cart->productCart()->detach();
                        $cart->delete();
                    }
                } else {
                    return response(['message' => 'El parametro sale_id es obligatorio'], 400);    
                }
            } else {
                return response(['message' => 'El proceso ya se completo'], 200);
            }

            if(isset($sale)){
                $response = ['res' => ['message'  => 'Venta registrada correctamente'], 'status' => 201];
            } else { 
                $response = ['res' => ['message' => 'Hubo un error al registrar la venta, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);

        } else {
            return $this->abortResponse('There was a problem with token validation');
        }
    }

    // Wompi request (event) handler
    public function store(Request $req){
        $body = $req->toArray();
        $email = $body['data']['transaction']['customer_email'];
        $idNumber = isset($body['data']['transaction']['customer_data']['legal_id']) 
        ? $body['data']['transaction']['customer_data']['legal_id']
        : $body['data']['transaction']['payment_method']['user_legal_id'];
        $paymentId = $body['data']['transaction']['id'];
        $paymentMethod = $body['data']['transaction']['payment_method_type'];
        $refPayment = $body['data']['transaction']['reference'];
        $totalPayment = $body['data']['transaction']['amount_in_cents'];
        $datetime = $body['data']['transaction']['created_at'];
        $status = $body['data']['transaction']['status'];
        $customerId = $this->getCustomer($email, $idNumber);
        
        $datetime = new DateTime($datetime);
        $createdAt = $datetime->format('Y-m-d');

        $sale = Sales::create([
            'id' => $paymentId,
            'idcliente' => $customerId,
            'metodopago' => $paymentMethod,
            'referenciapago' =>$refPayment,
            'totalpago' => $totalPayment / $this->cents,
            'estado' => $status,
            'fechacreacion' => date($createdAt),
            'fechamodificacion' => date($createdAt)
        ]);

        Log::info($sale);
        return response('', 200);
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $sale = Sales::where('id', $id)->first();

            if($sale){
                $customer = $sale->customer()->first();
                $person = $customer->person()->first();
                $address = $person->address()->get();
                $numbers = $person->phone()->get();
                $products = $sale->productSale()->get();
                $sale->direccion = $address;
                $sale->numeros = $numbers;
                $sale->productos = $products;
                $sale->usuario =  array_merge($customer->toArray(), $person->toArray());
            }

            if(isset($sale)){
                $response = ['res' => ['data' => $sale], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion de la venta, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);

        } else {
            $this->abortResponse("Forbidden");
        }
    }

    public function update(Request $req, $id){
        if($req->permissions['update']){
            $validator = $this->validateFields->validate($req, ['estimated_date', 'guide_number', 'carrier']);
            if($validator) return response($validator['res'], $validator['status']);

            $shipping = Sales::where('id', $id)->update([
                'fechaestimada' => DateTime::createFromFormat('Y-m-d', $req->estimated_date)->format('Y-m-d'),
                'guiaenvio' => $req->guide_number,
                'transportadora' => $req->carrier,
                'fechamodificacion' => date('Y-m-d')
            ]);

            if(isset($shipping)){
                $response = ['res' => ['message' => 'Los datos de envio fueron creados correctamente'], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear los datos de envio, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else {
            $this->abortResponse("Forbidden");
        }
    }
}
