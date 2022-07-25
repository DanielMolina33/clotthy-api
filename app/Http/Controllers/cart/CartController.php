<?php

namespace App\Http\Controllers\cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\cart\Cart;
use App\Models\products\Products;

class CartController extends Controller {
    private $validateField;

    public function __construct(){
        $this->validateFields = new ValidateFields();
    }

    private function checkIfCartExists($customerId){
        $cart = Cart::where('idcliente', $customerId)->first();
        return $cart ? $cart : false;
    }

    private function checkIfProductExists($productId){
        $product = Products::where('id', $productId)->first();
        return $product ? $product : false;
    }

    private function setProductCartId($req, $cart){
        $productsCart = $cart->productCart()->get()->toArray();

        foreach($productsCart as $key => $productCart){
            if($productCart['id'] == $req->id_prod_cart){
                $productCartId = $productCart['pivot']['id'];
                $req->merge(['product_cart_id' => $productCartId]);
                break;
            }
        }
    }

    public function index(Request $req){
        $customerId = $req->user()->id;
        $cart = Cart::where('idcliente', $customerId)->first();

        if($cart){
            $products = $cart->productCart()->get()->map(function($item){ 
                $item->cantidad = $item->pivot->cantidadproductos;
                unset($item->pivot);
                return $item; 
            });

            $cart->productos = $products;
        }

        if(isset($cart)){
            $response = ['res' => ['data'  => $cart], 'status' => 200];
        } else { 
            $response = ['res' => ['message' => 'El carrito esta vacio'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function create(){
        //
    }

    public function store(Request $req){
        $customer = $req->user();
        $customerId = $customer->id;
        $cityId = $customer->person->idciudad;
        
        if($cityId){
            $cart = $this->checkIfCartExists($customerId);
            if($cart) $req->merge(['cart_id' => $cart->id]);
    
            $validator = $this->validateFields->validate($req, ['id_prod_cart', 'prod_amount']);
            if($validator) return response($validator['res'], $validator['status']);
    
            $product = $this->checkIfProductExists($req->id_prod_cart);
    
            if(!$cart){
                $cart = Cart::create([
                    'idcliente' => $customerId,
                    'estado' => 1,
                    'fechacreacion' => date('Y-m-d'),
                    'fechamodificacion' => date('Y-m-d')
                ]);
            }
    
            if($product){
                $cart->productCart()->attach($req->id_prod_cart, [
                    'idprod' => $req->id_prod_cart,
                    'idcarrito' => $cart->id,
                    'cantidadproductos' => $req->prod_amount,
                    'estado' => 1,
                    'fechacreacion' => date('Y-m-d'),
                    'fechamodificacion' => date('Y-m-d')
                ]);
            } else {
                return response(['message' => 'El producto que estas tratando de agregar al carrito no existe'], 400);
            }
    
            if(isset($cart)){
                $response = ['res' => ['data'  => $cart->id], 'status' => 201];
            } else { 
                $response = ['res' => ['message' => 'Hubo un error al agregar el producto al carrito, intentalo de nuevo'], 'status' => 400];
            }
            
        } else {
            $response = ['res' => ['message' => 'En tu perfil, elige una ciudad. Ello nos ayuda a calcular el costo de envio'], 'status' => 400];
        }

        return response($response['res'], $response['status']);
    }

    public function show($id){
        //
    }

    public function edit($id){
        //
    }

    public function update(Request $req, $id){
        if($req->isMethod('PUT')){
            $customerId = $req->user()->id;
            $cart = Cart::where('id', $id)->where('idcliente', $customerId)->first();

            if($cart){
                $this->setProductCartId($req, $cart);
                $validator = $this->validateFields->validateCart($req, ['id_prod_cart', 'prod_amount'], $cart);
                if($validator) return response($validator['res'], $validator['status']);

                if($req->prod_amount == 0){
                    $response = $this->deleteOneProduct($req, $id);
                    return response($response['res'], $response['status']);
                } else {
                    $cart->productCart()->updateExistingPivot($req->id_prod_cart, [
                        'cantidadproductos' => $req->prod_amount,
                        'fechamodificacion' => date('Y-m-d')
                    ]);
                }
            }
    
            if(isset($cart)){
                $response = ['res' => ['message' => 'Producto actualizado correctamente'], 'status' => 200];
            } else { 
                $response = ['res' => ['message' => 'Hubo un error al actualizar el producto, intentalo de nuevo'], 'status' => 400];
            }
    
            return response($response['res'], $response['status']);

        } else if($req->isMethod('PATCH')){
            $response = $this->deleteOneProduct($req, $id);

            return response($response['res'], $response['status']);
        }
    }

    public function deleteOneProduct($req, $id){
        $customerId = $req->user()->id;
        $cart = Cart::where('id', $id)->where('idcliente', $customerId)->first();

        if($cart){
            $this->setProductCartId($req, $cart);
            $validator = $this->validateFields->validateCart($req, ['id_prod_cart'], $cart);
            if($validator) return ['res' => $validator['res'], 'status' => $validator['status']];

            $productsCart = $cart->productCart()->get()->toArray();

            if($productsCart){  
                foreach($productsCart as $key => $productCart){
                    if($productCart['id'] == $req->id_prod_cart){
                        unset($productsCart[$key]);
                        $cart->productCart()->detach($req->id_prod_cart);
                        break;
                    }
                }

                if(count($productsCart) == 0){
                    return $this->destroy($req, $cart->id, false);
                }

            } else {
                return $this->destroy($req, $cart->id, false);
            }
        }

        if(isset($cart)){
            return ['res' => ['message' => 'Producto eliminado del carrito'], 'status' => 200];
        } else { 
            return ['res' => ['message' => 'Hubo un error al eliminar el producto del carrito, intentalo de nuevo'], 'status' => 400];
        }
    }

    public function destroy(Request $req, $id, $fromReq=true){
        $customerId = $req->user()->id;
        $cart = Cart::where('id', $id)->where('idcliente', $customerId)->first();

        if($cart){
            $cart->productCart()->detach();
            $cart->delete();
        }

        if(isset($cart)){
            $response = ['res' => ['message' => 'El carrito se vacio correctamente'], 'status' => 200];
        } else { 
            $response = ['res' => ['message' => 'No se pudo vaciar el carrito'], 'status' => 400];
        }
        
        if($fromReq) return response($response['res'], $response['status']);
        else return $response;
    }
}
