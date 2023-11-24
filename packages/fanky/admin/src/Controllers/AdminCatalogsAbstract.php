<?php namespace Fanky\Admin\Controllers;

use Illuminate\Database\Eloquent\Model;
use Request;

class AdminCatalogsAbstract extends AdminController {

	public function getItems(Model $model) {

		$items = $model::orderBy('order');
		if(Request::has('select_category')){
			$items->whereCategoryId(Request::get('select_category'));
		};
		$items = $items->with('category')->get();

		return $items;
	}

	public function getCategories(Model $model) {
		$categories = $model::orderBy('order')->get();

		return $categories;
	}

	public function getModel(Model $model, $id = null) {
		if($id == null){
			return $model;
		} else {
			$item = $model::find($id);
			return (!$item)? null: $item;
		}
	}

	public function orderModel(Model $model, $sorted) {
		foreach ($sorted as $order => $id) {
			$model::whereId($id)->update(['order' => $order]);
		}
	}

	public function deleteModel(Model $model, $id) {
		$item = $this->getModel($model, $id);
		if(!$item) return abort(404);

		$item->delete();
		return ['success' => true];
	}
}