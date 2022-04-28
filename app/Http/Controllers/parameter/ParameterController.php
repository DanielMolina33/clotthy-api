<?php

namespace App\Http\Controllers\parameter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\validations\ValidateFields;

class ParameterController extends Controller {
    private $validateFields;

    public function __construct(){
        $this->validateFields = new ValidateFields();
    }

    public function index(){
        //
    }

    public function create(){
        //
    }

    public function store(Request $request){
        //
    }

    public function show($id){
        //
    }

    public function edit($id){
        //
    }

    public function update(Request $request, $id){
        //
    }

    public function destroy($id){
        //
    }
}
