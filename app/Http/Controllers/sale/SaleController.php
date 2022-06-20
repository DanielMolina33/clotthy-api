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

class SaleController extends Controller {
    private $cents;

    public function __construct(){
        $this->cents = 100;
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
        // $email = $body['data']['transaction']['customer_email'];
        // $idNumber = $body['data']['transaction']['customer_data']['legal_id'];
        $paymentId = $body['data']['transaction']['id'];
        $paymentMethod = $body['data']['transaction']['payment_method_type'];
        $refPayment = $body['data']['transaction']['reference'];
        $totalPayment = $body['data']['transaction']['amount_in_cents'];
        $datetime = $body['data']['transaction']['created_at'];
        $status = $body['data']['transaction']['status'];
        // $customerId = $this->getCustomer($email, $idNumber);
        
        $datetime = new DateTime($datetime);
        $createdAt = $datetime->format('Y-m-d');

        $sale = Sales::create([
            'id' => $paymentId,
            'idcliente' => null,
            'metodopago' => $paymentMethod,
            'referenciapago' =>$refPayment,
            'totalpago' => $totalPayment / $this->cents,
            'estado' => $status,
            // 'fechacreacion' => date($createdAt),
            'fechamodificacion' => date($createdAt)
        ]);

        Log::info($sale);
        return response('', 200);
    }
}
