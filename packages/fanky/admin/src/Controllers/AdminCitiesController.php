<?php namespace Fanky\Admin\Controllers;

use Config;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\SxgeoCity;
use Fanky\Admin\Models\SxgeoCountry;
use Fanky\Admin\Models\SxgeoRegion;
use Request;
use Text;
use Validator;

class AdminCitiesController extends AdminController {

	public function getIndex() {
		$cities = City::orderBy('name')->get();

		return view('admin::cities.main', ['cities' => $cities]);
	}

	public function getEdit($id = null) {
		if (!$id || !($city = City::find($id))) {
			$city = new City;
		}

        $sxgeo_cities = $city->sxgeo_cities()->get();
        $api_key = Config::get('google.api_key');

		return view('admin::cities.edit', ['city' => $city, 'sxgeo_cities' => $sxgeo_cities, 'api_key' => $api_key]);
	}

	/**
	 * @return array
	 */
	public function postSave() {
		$id = Request::input('id');
		$data = Request::only([
		    'name', 'alias', 'in_city', 'from_city', 'long', 'lat',
            'title', 'keywords', 'description', 'text',
        ]);
		$sxgeo_city_ids = Request::get('sxgeo_city_id');
		if (!$sxgeo_city_ids) {
			$sxgeo_city_ids = [];
		} else {
			$sxgeo_city_ids = explode(',', $sxgeo_city_ids);
		}

		if (!$data['alias']) $data['alias'] = Text::translit($data['name']);
		// валидация данных
		$rules = [
			'name'          => 'required',
			'alias'         => 'required|unique:cities,alias' . (($id) ? ',' . $id : ''),
		];
		$validator = Validator::make(
			$data + ['sxgeo_city_id' => $sxgeo_city_ids],
			$rules
		);

		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

		// сохраняем страницу
		$city = City::find($id);
		if (!$city) {
			$city = City::create($data);
			$city->sxgeo_cities()->sync($sxgeo_city_ids);
			return ['redirect' => route('admin.cities.edit', [$city->id])];
		} else {
			$city->update($data);
			$city->sxgeo_cities()->sync($sxgeo_city_ids);
		}

		return ['msg' => 'Изменения сохранены.'];
	}

	public function postDelete($id) {
		$article = City::find($id);
		$article->delete();

		return ['success' => true];
	}

	public function postTree($city_id = null) {
		$tree = [];
		$countries = SxgeoCountry::whereIn('iso', ['RU'])->get(['name_ru', 'id', 'iso']);
		/** @var City $city */
		$city = City::find($city_id);
		$sxgeo_cities = (!$city) ? [] : $city->sxgeo_cities()->pluck('id')->all();
		foreach ($countries as $country) {
			$count_cities = 0;
			$item = [
				'text'       => $country->name_ru,
				'selectable' => false,
				'expanded'   => true,
				'value'      => $country->iso,
				'type'       => 'country',
				'nodes'      => $this->get_regions($country->iso, $sxgeo_cities, $count_cities),
				'state'      => array('expanded' => false),
			];
			if ($count_cities) {
				$item['tags'] = array($count_cities);
			}
			$tree[] = $item;
		}

		return ['tree' => $tree];
	}

	private function get_regions($country_iso, $sxgeo_cities, &$count_cities) {
		$result = [];
		$regions = SxgeoRegion::whereCountry($country_iso)
			->orderBy('name_ru')->get(['name_ru', 'id']);
		foreach ($regions as $region) {
			$region_arr = [
				'text'       => $region->name_ru,
				'selectable' => false,
				'expanded'   => false,
				'value'      => $region->id,
				'type'       => 'region',
			];
			$count_select = 0;
			$cities = $this->get_cities($region->id, $sxgeo_cities, $count_select);
			if (count($cities)) {
				$region_arr['nodes'] = $cities;
				if ($count_select) {
					$region_arr['tags'] = array($count_select);
				}
			}
			$result[] = $region_arr;
			$count_cities += $count_select;
		}

		return $result;
	}

	private function get_cities($region_id, $sxgeo_cities, &$count_cities) {
		$result = [];
		$cities = SxgeoCity::whereRegionId($region_id)
			->orderBy('name_ru')
			->get();
		foreach ($cities as $city) {
			$city_arr = [
				'text'       => $city->name_ru,
				'selectable' => false,
				'expanded'   => false,
				'value'      => $city->id,
				'type'       => 'city',
				//'tags'			=> array('city'),
			];
			if (count($sxgeo_cities) && in_array($city->id, $sxgeo_cities)) {
				$city_arr['state']['checked'] = true;
				$count_cities++;
			}
			$result[] = $city_arr;
		}

		return $result;
	}
}
