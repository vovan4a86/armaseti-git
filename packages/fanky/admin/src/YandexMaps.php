<?php namespace Fanky\Admin;

use Cache;

class YandexMaps {

	/**
	 * Использование -
	 * $str = 'Екатеринбург, Мамина-Сибиряка 85';
	 * $pos_arr = YandexMaps::getPos($str);
	 * результат - массив объектов
	 *
	 *
	 * @param $str
	 *
	 * @return array+
	 */
	public static function getPos($str){
		$curl = curl_init();
		$geocode = str_replace(' ','+',$str);
		$response = Cache::get('ymaps_' . md5($geocode), '');
		if(!$response){
			$url = "https://geocode-maps.yandex.ru/1.x/?geocode=$geocode&lang=ru_RU&format=json";
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response);
			Cache::put('ymaps_' . md5($geocode),$response,43200); // на месяц кешируем
		}
		$count = $response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
		if($count){
			$result = [];
			$items = $response->response->GeoObjectCollection->featureMember;
			foreach ( $items as $obj) {
				list($lat, $long) = explode(' ', $obj->GeoObject->Point->pos);
				$result[] = [
					'name'			=> $obj->GeoObject->name,
					'description'	=> $obj->GeoObject->description,
					'lat'			=> $lat,
					'long'			=> $long,
				];
			}
			return $result;

		} else {
			return [];
		}
	}
}
