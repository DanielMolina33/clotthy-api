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
		->select(
			'productos.id',
			'productos.color',
			'productos.talla',
			'productos.subcategoria',
			'productos.referenciaprod',
			'productos.nombreprod',
			'productos.descripcionprod',
			'productos.stock',
			'productos.preciounitario',
			'productos.preciofinal',
			'productos.preciodescuento',
			'productos.porcentajedescuento',
			'productos.existenciaprod',
			'productos.total',
			'productos.imgprod1',
			'productos.imgprod2',
			'productos.imgprod3',
			'productos.imgprod4',
			'productos.fechacreacion',
			'productos.fechamodificacion',
			'escategoria'
			)
		->join('tipos', $this->table.'.subcategoria', '=', 'tipos.id')
		->join('tipo', 'tipos.idtipo', '=', 'tipo.id')
		->whereRaw($rule)
		->orderByRaw("productos.preciofinal $orderBy")
		->get()->map(function($item){
			$item->id = (int)$item->id;
			$item->color = (int)$item->color;
			$item->talla = (int)$item->talla;
			$item->subcategoria = (int)$item->subcategoria;
			$item->stock = (int)$item->stock;
			$item->preciounitario = (int)$item->preciounitario;
			$item->preciofinal = (int)$item->preciofinal;
			$item->preciodescuento = (int)$item->preciodescuento;
			$item->porcentajedescuento = (int)$item->porcentajedescuento;
			$item->existenciaprod = (int)$item->existenciaprod;
			$item->total = (int)$item->total;
			return $item;
		});

		return $filteredItems;
	}

	public function filterItems($req, $orderBy='asc', $cat=null, $subcat=null){
		$rule = null;

		if($cat && $subcat){
			$rule = "tipo.nombretipo = '$cat' and tipos.nombretipos = '$subcat' and productos.estado = 1";
		} else if($cat){
			$rule = "tipo.nombretipo = '$cat' and productos.estado = 1";
		} else if($subcat){
			$rule = "tipos.nombretipos = '$subcat' and productos.estado = 1";
		}

		if($rule){
			return $this->all($req, $rule, $orderBy);
		} else {
			return $this->order($req, $orderBy);
		}
	}
}