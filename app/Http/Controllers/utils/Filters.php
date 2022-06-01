<?php

namespace App\Http\Controllers\utils;

use Illuminate\Support\Facades\DB;
use App\Models\products\Products;

class Filters {
	private $table;
	private $pagination;

	public function __construct(){
		$this->table = 'productos';
		$this->pagination = env('PAGINATION_PER_PAGE');
	}

	private function order($req, $descOrAsc){
		$filteredPrice = DB::table($this->table)
		->orderByRaw("productos.preciofinal $descOrAsc")
		->get();

		return $filteredPrice;
	}

	private function all($req, $rule, $orderBy){
		$filteredItems = DB::table($this->table)
		->join('tipos', $this->table.'.subcategoria', '=', 'tipos.id')
		->join('tipo', 'tipos.idtipo', '=', 'tipo.id')
		->whereRaw($rule)
		->orderByRaw("productos.preciofinal $orderBy")
		->get()->map(function($item){
			unset($item->estado);
			unset($item->idtipo);
			unset($item->nombretipos);
			unset($item->descripciontipos);
			unset($item->nombretipo);
			unset($item->descripciontipo);
			return $item;
		});

		return $filteredItems;
	}

	public function filterItems($req, $orderBy='asc', $cat=null, $subcat=null){
		$rule = null;

		if($cat && $subcat){
			$rule = "tipo.nombretipo = '$cat' and tipos.nombretipos = '$subcat'";
		} else if($cat){
			$rule = "tipo.nombretipo = '$cat'";
		} else if($subcat){
			$rule = "tipos.nombretipos = '$subcat'";
		}

		if($rule){
			return $this->all($req, $rule, $orderBy);
		} else {
			return $this->order($req, $orderBy);
		}
	}
}