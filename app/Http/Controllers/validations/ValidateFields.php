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

    private function validatePhone($req, $field){
        $isRequired = $field == 'cellphone' ? 'required' : '';
        $validator = Validator::make($req->only($field),
            [$field => "$isRequired|max:20|numeric"],
        );
        $this->getErrors($validator->errors()->all());
    }

    private function validateForeignId($req, $field){
        $validator = Validator::make($req->only($field),
            [$field => 'required|integer'],
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
                    $unique = $userType !== null ? '|unique:'.$userType : "";
                    $validator = Validator::make($req->only('email'),
                        ['email' => 'required|string|email|max:45'.$unique],
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
                    $this->validateForeignId($req, 'id_type');
                    break;
                case 'id_city':
                    $this->validateForeignId($req, 'id_city');
                    break;
                case 'id_gender':
                    $this->validateForeignId($req, 'id_gender');
                    break;
                case 'first_name':
                    $this->validateString($req, 'first_name');
                    break;
                case 'last_name':
                    $this->validateString($req, 'last_name');
                    break;
                case 'id_number':
                    $validator = Validator::make($req->only('id_number'),
                        ['id_number' => 'required|max:45|alpha_num|unique:personas,numerodocumento'],
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
                    $this->validatePhone($req, 'cellphone');
                    break;
                case 'phone':
                    $this->validatePhone($req, 'phone');
                    break;
                case 'birthday':
                    $validator = Validator::make($req->only('birthday'),
                        ['birthday' => "required|date|date_format:Y-m-d|after_or_equal:$this->someTimeAgo|before_or_equal:$this->today"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'avatar':
                    $validator = Validator::make($req->only('avatar'),
                        ['avatar' => "mimes:jpg,jpeg,png,webp|max:2000"]
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'parameter_name':
                    $this->validateString($req, 'parameter_name');
                    break;
                case 'parameter_desc':
                    $this->validateString($req, 'parameter_desc', 300);
                    break;
                case 'id_address_type':
                    $this->validateForeignId($req, 'id_address_type');
                    break;
                case 'postal_code':
                    $validator = Validator::make($req->only('postal_code'),
                        ['postal_code' => 'numeric|max:45']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
                case 'complements':
                    $validator = Validator::make($req->only('complements'),
                        ['complements' => 'max:300|string']
                    );
                    $this->getErrors($validator->errors()->all());
                    break;
            }
        }

        if(count($this->messages) > 0){
            return ['res' => ['message' => $this->messages],'status' => 200];
        } else {
            return false;
        }
    }
}
