<?php

  namespace App\Http\Controllers\utils;

  class StoreSocialMedia {
    public static function store($req, $model, $option){
      $data = self::getSmData($req);
      
      foreach($data as $key => $item){
        if($option == 'create'){
          self::create($model, [
            'idempresa' => $model->id,
            'nombrered' => $item['name'],
            'enlacered' => $item['url'],
            'estado' => 1,
            'fechacreacion' => date('Y-m-d'),
            'fechamodificacion' => date('Y-m-d')
          ]);

        } else if($option == 'update'){
          $smId = $model->socialMedia()->where('idempresa', $model->id)->get()[$key]->id;
          self::update($model, $smId, [
            'idempresa' => $model->id,
            'nombrered' => $item['name'],
            'enlacered' => $item['url'],
            'fechamodificacion' => date('Y-m-d')
          ]);
        }
      }
    }

    private static function create($model, $data){
      $model->socialMedia()->create($data);
    }

    private static function update($model, $smId, $data){
      $sm = $model->socialMedia()->where('id', $smId)->update($data);
    }

    private static function getSmData($req){
      $smData = [];
      $length = intval($req->sm_length);
      for($i = 1; $i <= $length; $i++){
        $nameField = "sm_name_$i";
        $urlField = "sm_url_$i";
        $name = $req->$nameField;
        $url = $req->$urlField;
        array_push($smData, ['name' => $name, 'url' => $url]);
      }

      return $smData;
    }
  }