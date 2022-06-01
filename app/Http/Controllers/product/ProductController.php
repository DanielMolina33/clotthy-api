<?php

namespace App\Http\Controllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;
use App\Models\products\Products;
use App\Http\Controllers\image\ImageController;
use App\Http\Controllers\utils\Filters;
use App\Http\Controllers\utils\Observations;

class ProductController extends Controller {
    private $filters;
    private $validateFields;

    public function __construct(){
        $this->filters = new Filters();
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
            $orderBy = $req->query('order_by');
            $category = $req->query('category');
            $subcategory = $req->query('subcategory');
            $search = $req->query('search');

            $products = Products::where('estado', 1)->get();

            if($orderBy || $category || $subcategory){
                $products = $this->filters->filterItems($req, $orderBy, $category, $subcategory);
            }

            if($search){
                $products = $products->filter(function($item) use($search) {
                    return str_contains(strtolower($item->nombreprod), strtolower($search)) ? $item->nombreprod : null;
                });
            }

            $products = $products->paginate($pagination);
            
            if(isset($products)){
                $response = ['res' => ['data' => $products], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion de los productos, intentalo de nuevo'], 'status' => 400];
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
                'id_color', 'id_size', 'id_subcategory', 'id_order', 'prod_ref', 'prod_name', 'image',
                'invoice', 'prod_desc', 'stock', 'purchase_price', 'profit_percent', 'observations',
            ]);
            if($validator) return response($validator['res'], $validator['status']);

            $product = Products::create([
                'color' => $req->id_color,
                'talla' => $req->id_size,
                'subcategoria' => $req->id_subcategory,
                'referenciaprod' => $req->prod_ref,
                'nombreprod' => $req->prod_name,
                'descripcionprod' => $req->prod_desc,
                'stock' => $req->stock,
                'preciofinal' => 0,
                'existenciaprod' => 0,
                'imgprod1' => '',
                'estado' => 1,
                'fechacreacion' => date('Y-m-d'),
                'fechamodificacion' => date('Y-m-d')
            ]);

            if($product){
                $inventory = $product->inventory()->create([
                    'idprod' => $product->id,
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
                    $inventory->urlfactura = ImageController::storeImage("products/$product->id/invoices", $req->file('invoice'));
                    $inventory->save();
                }

                foreach($req->images as $i => $image){
                    $field = 'imgprod'.$i+1;
                    $product->$field = ImageController::storeImage("products/$product->id/images", $image);
                }
                $product->save();
            }

            if(isset($product)){
                $response = ['res' => ['message' => 'La informacion del producto fue creada correctamente'], 'status' => 201];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al crear la informacion del producto, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);
            
        } else {
            return $this->abortResponse();
        }
    }

    public function show(Request $req, $id){
        if($req->permissions['read']){
            $product = Products::where('estado', 1)->where('id', $id)->first();

            if($product){
                $comments = $product->comment()->get();
                $ratings = $product->rating()->get();

                $product->comentarios = $comments;
                $product->calificaciones = $ratings;
            }

            if(isset($product)){
                $response = ['res' => ['data' => $product], 'status' => 200];
            } else {
                $response = ['res' => ['message' => 'Hubo un error al obtener la informacion del producto, intentalo de nuevo'], 'status' => 400];
            }

            return response($response['res'], $response['status']);
        } else {
            return $this->abortResponse();
        }
    }

    public function edit($id){
        //
    }

    // _method: PUT required field
    public function update(Request $req, $id){
        if($req->permissions['update']){
            if($req->isMethod('PUT')){
                $validator = $this->validateFields->validate($req, [
                    'id_color', 'id_size', 'id_subcategory', 'prod_ref', 
                    'prod_name', 'prod_desc', 'stock', 'offer_percent', 'image'
                ]);
                if($validator) return response($validator['res'], $validator['status']);
    
                $product = Products::where('id', $id)->first();
    
                if($product){
                    $product->update([
                        'color' => $req->id_color,
                        'talla' => $req->id_size,
                        'subcategoria' => $req->id_subcategory,
                        'referenciaprod' => $req->prod_ref,
                        'nombreprod' => $req->prod_name,
                        'descripcionprod' => $req->prod_desc,
                        'stock' => $req->stock,
                        'porcentajedescuento' => $req->offer_percent,
                        'fechamodificacion' => date('Y-m-d')
                    ]);
    
                    foreach($req->images as $i => $image){
                        $field = 'imgprod'.$i+1;
                        if($image){
                            $product->$field = ImageController::updateImage("products/$product->id/images", $product->$field, $image);
                        }
                    }
                    
                    $product->save();
                }
    
                if(isset($product)){
                    $response = ['res' => ['message' => 'La informacion del producto fue actualizada correctamente'], 'status' => 200];
                } else {
                    $response = ['res' => ['message' => 'Hubo un error al actualizar la informacion del producto, intentalo de nuevo'], 'status' => 400];
                }
    
                return response($response['res'], $response['status']);

            } else if($req->isMethod('PATCH')){
                $response = $this->destroy($req, $id);

                return response($response['res'], $response['status']);
            }

        } else {
            return $this->abortResponse();
        }
    }

    public function destroy($req, $id){
        if($req->permissions['delete']){
            $product = Products::where('id', $id)->first();

            if($product){
                $product->update([
                    'estado' => 0,
                    'fechamodificacion' => date('Y-m-d')
                ]);
            }

            if(isset($product)){
                return ['res' => ['message' => 'La informacion del producto fue eliminada correctamente'], 'status' => 200];
            } else {
                return ['res' => ['message' => 'Hubo un error al eliminar la informacion del producto, intentalo de nuevo'], 'status' => 400];
            }

        } else {
            return $this->abortResponse();
        }
    }
}
