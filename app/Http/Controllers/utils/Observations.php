<?php

namespace App\Http\Controllers\utils;

class Observations {
	public static function setObservation($req, $personId, $inventoryId){
		return [
			'idpersona' => $personId,
			'identradaproductos' => $inventoryId,
			'observacion' => $req->observations,
			'estado' => 1,
			'fechacreacion' => date('Y-m-d'),
			'fechamodificacion' => date('Y-m-d')
		];
	}
}