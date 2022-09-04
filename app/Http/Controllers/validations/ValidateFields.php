<?php

namespace App\Http\Controllers\validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidateFields {
    private $today, $someTimeAgo;
    private $messages = [];

    function __construct(){
        $this->today = date('Y-m-d');
        $this->someTimeAgo = date('Y-m-d', strtotime($this->today."- 120 year"));
        $this->extendValidations();
    }

    private function extendValidations(){
        Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });

        Validator::extend('only_letters', function($attr, $value){
            return preg_match_all('/^[a-zA-ZÀ-ÿ\s\ñ]+$/', $value);
        });

        Validator::extend('alpha_num_spaces', function($attr, $value){
            return preg_match_all('/^[a-zA-ZÀ-ÿ0-9\s\,\.\ñ]+$/', $value);
        });

        Validator::extend('only_numbers', function($attr, $value){
            return preg_match_all('/^[0-9]+$/', $value);
        });
    }

    private function getErrors($validations){
        foreach($validations as $message){
            array_push($this->messages, $message);
        }
    }

    private function validateString($req, $field, $length=45){
        $validator = Validator::make($req->only($field),
            [$field => "required|max:$length|only_letters"],
            ["$field.only_letters" => "El campo $field solo puede contener letras."]
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateAlphaNumSpaces($req, $field, $required='required', $length=45){
        $validator = Validator::make($req->only($field),
            [$field => "$required|max:$length|alpha_num_spaces"],
            ["$field.alpha_num_spaces" => "El campo $field No puede contener caracteres especiales"]
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateIndicative($req, $field){
        $name = 'indicative_'.$field;
        $validator = Validator::make($req->only($name),
            [$name => 'required|digits_between:0,5']
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateSocialMedia($req, $field, $name){
        if($field == 'url'){
            $validator = Validator::make($req->only($name),
                [$name => 'required|url|max:45']
            );
            $this->getErrors($validator->errors()->all());
        } else if($field == 'name'){
            $validator = Validator::make($req->only($name),
                [$name => 'required|max:45|without_spaces|alpha_num'],
                ["$name.without_spaces" => "Los espacios en blanco no son permitidos en el campo $name"]
            );
            $this->getErrors($validator->errors()->all());
        }
    }

    private function validateFieldLength($req, $field){
        if($field == 'cp'){
            if(is_numeric($req->cp_length) && strlen($req->cp_length) == 1){
                for($i = 1; $i <= $req->cp_length; $i++){
                    $name = $field.'_'.$i;
                    $this->validateIndicative($req, $name);
                    $validator = Validator::make($req->only($name),
                        [$name => "present|digits_between:0,20"]
                    );
                    $this->getErrors($validator->errors()->all());
                }
            }
        } else if($field == 'p'){
            if(is_numeric($req->p_length) && strlen($req->p_length) == 1){
                for($i = 1; $i <= $req->p_length; $i++){
                    $name = $field.'_'.$i;
                    $this->validateIndicative($req, $name);
                    $validator = Validator::make($req->only($name),
                        [$name => 'present|digits_between:0,20']
                    );
                    $this->getErrors($validator->errors()->all());
                }
            }
        } else if($field == 'sm'){
            if(is_numeric($req->sm_length) && strlen($req->sm_length) == 1){
                for($i = 1; $i <= $req->sm_length; $i++){
                    $url = $field.'_url_'.$i;
                    $name = $field.'_name_'.$i;
                    $this->validateSocialMedia($req, 'url', $url);
                    $this->validateSocialMedia($req, 'name', $name);
                }
            }
        }
    }

    private function validateValuesIn($req, $field, $values){
        $min = $values[0];
        $max = $values[1];

        $validator = Validator::make($req->only($field),
            [$field => "required|only_numbers|digits_between:$min,$max"],
            ["$field.only_numbers" => "El campo $field solo puede contener numeros enteros"]
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateRoleId($req){
        foreach($req->roles as $role){
            $validator = Validator::make(['role' => $role],
                ['role' => "required|numeric|gt:0"],
            );
            $this->getErrors($validator->errors()->all());
        }
    }

    private function validateForeignId($req, $field, $isCustomer=false){
        $isRequired = $isCustomer ? 'nullable' : 'required';
        $validator = Validator::make($req->only($field),
            [$field => "$isRequired|numeric|gt:0"],
        );
        $this->getErrors($validator->errors()->all());
    }

    function validate($req, $fields, $userType=null){
        foreach($fields as $field){
            switch($field){
                case 'username':
                    $validator = Validator::make($req->only('username'),
                        ['username' => 'required|max:45|without_spaces|alpha_num'],
                        [
                            'username.without_spaces' => 'Los espacios en blanco no son permitidos en el campo username'
                        ]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'email':
                    $userId = $req->route('person') ? (int)($req->route('person')) : (int)($req->user_id);
                    $isRequired = $userType !== 'clientes' ? 'required' : 'nullable';
                    $unique = $userType !== null ? "|unique:$userType,email,".$userId.",idpersona" : "";
                    $validator = Validator::make($req->only('email'),
                        ['email' => "$isRequired|string|email|max:100".$unique],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'supplier_email':
                    $supplierId = (int)($req->route('supplier'));
                    $validator = Validator::make($req->only('supplier_email'),
                        ['supplier_email' => "required|string|email|max:100|unique:proveedores,correoproveedor,$supplierId"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'password':
                    $validator = Validator::make($req->only('password', 'password_confirmation'),
                        ['password' => 'required|string|min:10|confirmed|without_spaces'],
                        ['password.without_spaces' => 'Los espacios en blanco no son permitidos en el campo password']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'id_type':
                    if($userType !== 'clientes') $this->validateForeignId($req, 'id_type');
                    else $this->validateForeignId($req, 'id_type', true);
                    break;
                case 'id_city':
                    if($userType !== 'clientes') $this->validateForeignId($req, 'id_city');
                    else $this->validateForeignId($req, 'id_city', true);
                    break;
                case 'id_gender':
                    if($userType !== 'clientes') $this->validateForeignId($req, 'id_gender');
                    else $this->validateForeignId($req, 'id_gender', true);
                    break;
                case 'first_name':
                    if($userType !== 'clientes') $this->validateString($req, 'first_name');
                    else $this->validateString($req, 'first_name');
                    break;
                case 'last_name':
                    if($userType !== 'clientes') $this->validateString($req, 'last_name');
                    else $this->validateString($req, 'last_name');
                    break;
                case 'id_number':
                    $userId = $req->route('person') ? (int)($req->route('person')) : (int)($req->user_id);
                    $isRequired = $userType !== 'clientes' ? 'required' : 'nullable';
                    $unique = $userType !== null ? "|unique:personas,numerodocumento,".$userId : "";
                    $validator = Validator::make($req->only('id_number'),
                        ['id_number' => "$isRequired|max:45|alpha_num".$unique],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'nit':
                    $companyId = (int)($req->route('company'));
                    $unique = "|unique:empresas,nitempresa,".$companyId;
                    $validator = Validator::make($req->only('nit'),
                        ['nit' => "required|max:45|alpha_num".$unique],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'address':
                    $validator = Validator::make($req->only('address'),
                        ['address' => 'required|max:45|string']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'cellphone':
                    $this->validateFieldLength($req, 'cp');
                    break;
                case 'cp_length':
                    $validator = Validator::make($req->only('cp_length'),
                        ['cp_length' => "required|digits:1"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'phone':
                    $this->validateFieldLength($req, 'p');
                    break;
                case 'p_length':
                    $validator = Validator::make($req->only('p_length'),
                        ['p_length' => 'nullable|digits:1']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'sm':
                    $this->validateFieldLength($req, 'sm');
                    break;
                case 'sm_length':
                    $validator = Validator::make($req->only('sm_length'),
                        ['sm_length' => 'required|digits:1']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'birthday':
                    $isRequired = $userType !=='clientes' ? 'required' : 'nullable';
                    $validator = Validator::make($req->only('birthday'),
                        ['birthday' => "$isRequired|date|date_format:Y-m-d|after_or_equal:$this->someTimeAgo|before_or_equal:$this->today"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'image':
                    $rule = ['image' => "nullable|mimes:jpg,jpeg,png,webp|max:2000"];
                    if(isset($req->images)){
                        foreach($req->images as $i => $image){
                            $isRequired = $i+1 == 1 ? 'required' : 'nullable';
                            $validator = Validator::make(['image' => $image],
                                ['image' => "$isRequired|mimes:jpg,jpeg,png,webp|max:2000"]
                            );
                            $this->getErrors($validator->errors()->all());
                        }
                    } else {
                        $validator = Validator::make($req->only('image'), $rule);
                        $this->getErrors($validator->errors()->all());
                    }

                    break;
                case 'invoice':
                    $validator = Validator::make($req->only('invoice'),
                        ['invoice' => 'required|mimes:pdf|max:2000']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'parameter_name':
                    $this->validateString($req, 'parameter_name');
                    break;
                case 'parameter_desc':
                    $this->validateAlphaNumSpaces($req, 'parameter_desc', 'nullable', 300);
                    break;
                case 'is_category':
                    $this->validateValuesIn($req, 'is_category', [0, 1]);
                    break;
                case 'id_address_type':
                    $this->validateForeignId($req, 'id_address_type');
                    break;
                case 'postal_code':
                    $validator = Validator::make($req->only('postal_code'),
                        ['postal_code' => 'nullable|digits_between:0,45']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'complements':
                    $validator = Validator::make($req->only('complements'),
                        ['complements' => 'nullable|max:300|string']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'pqrsf_status':
                    $this->validateForeignId($req, 'pqrsf_status');
                    break;
                case 'pqrsf_type':
                    $this->validateForeignId($req, 'pqrsf_type');
                    break;
                case 'user_id':
                    $this->validateForeignId($req, 'user_id');
                    break;
                case 'customer_id':
                    $this->validateForeignId($req, 'customer_id');
                    break;
                case 'subject':
                    $this->validateAlphaNumSpaces($req, 'subject');
                    break;
                case 'description':
                    $validator = Validator::make($req->only('description'),
                        ['description' => 'required|string|max:300']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'observations':
                    $validator = Validator::make($req->only('observations'),
                        ['observations' => "required|max:300|string"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'company_name':
                    $validator = Validator::make($req->only('company_name'),
                        ['company_name' => 'required|string|max:45']
                    );
                    $this->getErrors($validator->errors()->all());
                    // $this->validateAlphaNumSpaces($req, 'company_name', 'required');
                    break;
                case 'role_name':
                    $this->validateString($req, 'role_name');
                    break;
                case 'role_desc':
                    $this->validateAlphaNumSpaces($req, 'role_desc', 'nullable', 300);
                    break;
                case 'module_name':
                    $this->validateString($req, 'module_name');
                    break;
                case 'module_desc':
                    $this->validateAlphaNumSpaces($req, 'module_desc', 'nullable', 300);
                    break;
                case 'roles':
                    $this->validateRoleId($req);
                    break;
                case 'role_id':
                    $except = (int)($req->route('module_role'));
                    $moduleId = $req->module_id;
                    $validator = Validator::make($req->only('role_id'),
                        ['role_id' => "required|numeric|gt:0|unique:modulos_roles,idroles,$except,id,idmodulos,$moduleId"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'module_id':
                    $this->validateForeignId($req, 'module_id');
                    break;
                case 'create':
                    $this->validateValuesIn($req, 'create', [0, 1]);
                    break;
                case 'update':
                    $this->validateValuesIn($req, 'update', [0, 1]);
                    break;
                case 'read':
                    $this->validateValuesIn($req, 'read', [0, 1]);
                    break;
                case 'delete':
                    $this->validateValuesIn($req, 'delete', [0, 1]);
                    break;
                case 'id_color':
                    $this->validateForeignId($req, 'id_color');
                    break;
                case 'id_size':
                    $this->validateForeignId($req, 'id_size');
                    break;
                case 'id_subcategory':
                    $this->validateForeignId($req, 'id_subcategory');
                    break;
                case 'id_order':
                    $this->validateForeignId($req, 'id_order');
                    break;
                case 'id_prod':
                    $this->validateForeignId($req, 'id_prod');
                    break;
                case 'id_prod_cart':
                    $cartId = $req->route('cart') ? (int)($req->route('cart')) : $req->cart_id;
                    $except = $req->route('cart') ? $req->product_cart_id : null;

                    $validator = Validator::make($req->only('id_prod_cart'),
                        ['id_prod_cart' => "required|numeric|gt:0|unique:productos_carrito,idprod,$except,id,idcarrito,$cartId"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'prod_ref':
                    // $except = (int)($req->route('product'));
                    // $subcategoryId = (int)($req->id_subcategory);
                    $validator = Validator::make($req->only('prod_ref'),
                        ['prod_ref' => "required|max:45|alpha_num"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'prod_name':
                    if($req->prod_names){
                        foreach($req->prod_names as $prodName){
                            $validator = Validator::make(['prod_name' => $prodName],
                                ['prod_name' => 'required|max:45|alpha_num_spaces'],
                                ['prod_name.alpha_num_spaces' => 'El campo prod_names no puede contener caracteres especiales']
                            );
                            $this->getErrors($validator->errors()->all());
                        }
                    } else {
                        $this->validateAlphaNumSpaces($req, 'prod_name');
                    }
                    break;
                case 'prod_desc':
                    $validator = Validator::make($req->only('prod_desc'),
                        ['prod_desc' => "required|max:300|string"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'prod_amount':
                    if($req->prod_amounts){
                        foreach($req->prod_amounts as $prodAmount){
                            $validator = Validator::make(['prod_amount' => $prodAmount],
                                ['prod_amount' => 'required|only_numbers|digits_between:1,11'],
                                ['prod_amount.only_numbers' => "El campo prod_amounts solo puede contener numeros enteros"]
                            );
                            $this->getErrors($validator->errors()->all());
                        }
                    } else {
                        $this->validateValuesIn($req, 'prod_amount', [1, 11]);
                    }
                    break;
                case 'purchase_price':
                    $this->validateValuesIn($req, 'purchase_price', [1, 10]);
                    break;
                case 'profit_percent':
                    $this->validateValuesIn($req, 'profit_percent', [1, 11]);
                    break;
                case 'offer_percent':
                    $this->validateValuesIn($req, 'offer_percent', [1, 11]);
                    break;
                case 'id_supplier':
                    $this->validateForeignId($req, 'id_supplier');
                    break;
                case 'supplier_name':
                    $this->validateAlphaNumSpaces($req, 'supplier_name', 'nullable');
                    break;
                case 'supplier_desc':
                    $validator = Validator::make($req->only('supplier_desc'),
                        ['supplier_desc' => "nullable|max:300|string"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'agent':
                    $this->validateString($req, 'agent');
                    break;
                case 'order_status':
                    $this->validateForeignId($req, 'order_status');
                    break;
                case 'applicant':
                    $this->validateString($req, 'applicant');
                    break;
                case 'order_desc':
                    $validator = Validator::make($req->only('order_desc'),
                        ['order_desc' => "required|max:300|string"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'estimated_date':
                    $validator = Validator::make($req->only('estimated_date'),
                        ['estimated_date' => "required|date|date_format:Y-m-d"],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'guide_number':
                    $this->validateValuesIn($req, 'guide_number', [1, 45]);
                    break;
                case 'carrier':
                    $this->validateString($req, 'carrier');
                    break;
            }
        }

        if(count($this->messages) > 0){
            return ['res' => ['message' => $this->messages],'status' => 400];
        } else {
            return false;
        }
    }

    public function validateCart($req, $fields, $cart){
        $validator = $this->validate($req, $fields, null);

        if(!$validator){
            $productsCart = $cart->productCart()->get()->toArray();

            foreach($productsCart as $key => $productCart){
                if($productCart['id'] != $req->id_prod_cart){
                    if($key == count($productsCart)-1){
                        $validator = ['res' => ['message' => 'El producto no existe en el carrito'], 'status' => 400];
                    }
                } else {
                    break;
                }
            }
        }

        return $validator;
    }

    public function validateWithPhone($req, $fields, $phone_length, $sm_length=null, $userType=null){
        $cp_gt = null;
        $p_gt = null;
        $sm_gt = null;
        $messages = [];

        if(in_array('cp_length', $fields)){
            $cp_gt = intval($req->cp_length) > $phone_length;
            if($cp_gt) array_push($messages, "cp length no puede ser mayor que $phone_length");
        }

        if(in_array('p_length', $fields)){
            $p_gt = intval($req->p_length) > $phone_length;
            if($p_gt) array_push($messages, "p length no puede ser mayor que $phone_length");
        }

        if(in_array('sm_length', $fields)){
            $sm_gt = intval($req->sm_length) > $sm_length;
            if($sm_gt) array_push($messages, "sm length no puede ser mayor que $sm_length");
        }

        if($cp_gt || $p_gt || $sm_gt) return ['res' => ['message' => $messages], 'status' => 400];

        $validator = $this->validate($req, $fields, $userType);
        return $validator;
    }
}
