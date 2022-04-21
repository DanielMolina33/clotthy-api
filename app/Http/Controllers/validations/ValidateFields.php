<?php

namespace App\Http\Controllers\validations;

use Illuminate\Support\Facades\Validator;

class ValidateFields {
    private $messages = [];

    function __construct(){
        $this->withoutSpaces();
    }

    private function withoutSpaces(){
        Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });
    }

    private function getErrors($validations){
        foreach($validations as $message){
            array_push($this->messages, $message);
        }
    }

    function validate($req, $fields, $userType=null){
        foreach($fields as $field){
            switch($field){
                case 'username':
                    $validator = Validator::make($req->only('username'), 
                        ['username' => 'required|string|max:45|without_spaces'], 
                        ['username.without_spaces' => 'Whitespace not allowed in username']
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
                    $this->getErrors($validator->errors()->all());;
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
