<?php

function getUrlByPath($path){
	return \Fanky\Admin\Models\Page::getUrlByPath(['tablitsa_tsvetov_ral']);
}

function getCityAliases(){
	return App::make('cityAliases');
}

function getLastUpdateCatalog(){
	$timeStamp = time();
	$pricePath = resource_path('price.xlsx');
	if(\File::exists($pricePath)){
		$timeStamp = \File::lastModified($pricePath);
	}
	$updateTime = \Carbon\Carbon::createFromTimestamp($timeStamp);
	if($updateTime->diffInDays(\Carbon\Carbon::now(), true) > 4){
		$updateTime = \Carbon\Carbon::now()->subDays(3);
	}

	return $updateTime->format('d.m.Y');
}

function getCatalogUrlByType($type){
	$id = \Illuminate\Support\Arr::get(\Fanky\Admin\Models\Catalog::$catalogByType, mb_strtolower($type));
	if($catalog = \Fanky\Admin\Models\Catalog::find($id)){
		return $catalog->url;
	}

	return null;
}