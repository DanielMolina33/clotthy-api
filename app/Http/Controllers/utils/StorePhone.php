<?php

  namespace App\Http\Controllers\utils;

  use App\Models\parameters\ParametersValues;

  class StorePhone {
    public static function store($req, $model, $table, $phone_type, $option){
      $id_number_type;
      $data = self::getNumbersData($req, $phone_type);
      
      foreach($data as $key => $item){
        if($phone_type == 'cp'){
          $id_number_type = ParametersValues::select('id')->where('nombretipos', 'celular')->first();
        } else if($phone_type == 'p'){
          $id_number_type = ParametersValues::select('id')->where('nombretipos', 'telefono')->first();
        }

        if($option == 'create'){
          self::create($model, [
            'tiponumero' => $id_number_type->id,
            'idproveedor' => $table == 'proveedor' ? $model->id : null,
            'idempresa' => $table == 'empresa' ? $model->id : null,
            'idpersona' => $table == 'persona' ? $model->id : null,
            'numerotelefono' => $item['number'],
            'indicativo' => $item['indicative'],
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
          ]);

        } else if($option == 'update'){
          $phoneId = $model->phone()->where('idempresa', $model->id)->get()[$key]->id;

          self::update($model, $phoneId, [
            'tiponumero' => $id_number_type->id,
            'idproveedor' => $table == 'proveedor' ? $model->id : null,
            'idempresa' => $table == 'empresa' ? $model->id : null,
            'idpersona' => $table == 'persona' ? $model->id : null,
            'numerotelefono' => $item['number'],
            'indicativo' => $item['indicative'],
            'fechamodificacion' => date('Y-m-d')
          ]);
        }
      }
    }

    private static function create($model, $data){
      $model->phone()->create($data);
    }

    private static function update($model, $phoneId, $data){
      $model->phone()->where('id', $phoneId)->update($data);
    }

    private static function getNumbersData($req, $name){
      $numbersData = [];
      $field = $name.'_length';
      $length = intval($req->$field);
      for($i = 1; $i <= $length; $i++){
        $numberField = $name.'_'.$i;
        $indicativeField = 'indicative_'.$name.'_'.$i;
        $number = $req->$numberField;
        $indicative = $req->$indicativeField;
        array_push($numbersData, ['number' => $number, 'indicative' => $indicative]);
      }

      return $numbersData;
    }
  }