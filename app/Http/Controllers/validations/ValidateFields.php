<?php

namespace App\Http\Controllers\validations;

use Illuminate\Support\Facades\Validator;

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
            return preg_match_all('/^[a-zA-ZÀ-ÿ\s]+$/', $value);
        });
        
        Validator::extend('alpha_num_spaces', function($attr, $value){
            return preg_match_all('/^[a-zA-ZÀ-ÿ0-9\s\,\.]+$/', $value);
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
            ["$field.only_letters" => "The $field must only contain letters."]
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateAlphaNumSpaces($req, $field, $required='required', $length=45){
        $validator = Validator::make($req->only($field),
            [$field => "$required|max:$length|alpha_num_spaces"],
            ["$field.alpha_num_spaces" => "$field cannot contain special characters"]
        );
    $this->getErrors($validator->errors()->all());
    }

    private function validateIndicative($req, $field){
        $name = 'indicative_'.$field;
        $validator = Validator::make($req->only($name),
            [$name => 'present|digits_between:0,5']
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateNumberInfo($req, $field){
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
                            'username.without_spaces' => 'Whitespace not allowed in username'
                        ]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'email':
                    // email length 100 in Database
                    $userId = (int)($req->route('person'));
                    $isRequired = $userType !== 'clientes' ? 'required' : 'nullable';
                    $unique = $userType !== null ? "|unique:$userType,email,".$userId : "";
                    $validator = Validator::make($req->only('email'),
                        ['email' => "$isRequired|string|email|max:100".$unique],
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'password':
                    $validator = Validator::make($req->only('password', 'password_confirmation'),
                        ['password' => 'required|string|min:10|confirmed|without_spaces'],
                        ['password.without_spaces' => 'Whitespace not allowed in password']
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
                    else $this->validateString($req, 'first_name', true);
                    break;
                case 'last_name':
                    if($userType !== 'clientes') $this->validateString($req, 'last_name');
                    else $this->validateString($req, 'last_name', true);
                    break;
                case 'id_number':
                    $userId = (int)($req->route('person'));
                    $isRequired = $userType !== 'clientes' ? 'required' : 'nullable';
                    $unique = $userType !== null ? "|unique:personas,numerodocumento,".$userId : "";
                    $validator = Validator::make($req->only('id_number'),
                        ['id_number' => "$isRequired|max:45|alpha_num".$unique],
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
                    $this->validateNumberInfo($req, 'cp');
                    break;
                case 'cp_length':
                    $validator = Validator::make($req->only('cp_length'),
                        ['cp_length' => "required|digits:1"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'phone':
                    $this->validateNumberInfo($req, 'p');
                    break;
                case 'p_length':
                    $validator = Validator::make($req->only('p_length'),
                        ['p_length' => 'nullable|digits:1']
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
                    $validator = Validator::make($req->only('image'),
                        ['image' => "nullable|mimes:jpg,jpeg,png,webp|max:2000"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'parameter_name':
                    $this->validateString($req, 'parameter_name');
                    break;
                case 'parameter_desc':
                    $this->validateAlphaNumSpaces($req, 'parameter_desc', 'nullable', 300);
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
                    $this->validateAlphaNumSpaces($req, 'observations', 'required', 300);
                    break;
            }
        }

        if(count($this->messages) > 0){
            return ['res' => ['message' => $this->messages],'status' => 400];
        } else {
            return false;
        }
    }
}
