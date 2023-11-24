<?php namespace App\Http\Middleware;

use Closure;
use App;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Settings;
use View;

class CityMiddleware {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {

		$current_city = new City();
		if($city_alias = $request->route()->parameter('city')){
			$request->route()->forgetParameter('city');
			$current_city = City::current($city_alias, true, $request);
		} else {
			$region_page = true;
			$segments = $request->segments();
			if(count($segments) > 0){
				$start = array_shift($segments);
				if(in_array($start, Page::$excludeRegionAlias)){
					$region_page = false;
				}
			}
//          убрал, потому что на оформлении заказа вылазило окно выбора города
//			if($region_page){
//				$temp_city = City::current(null, true, $request);
//				if($request->decodedPath() == '/'){
//					$current_city = $temp_city;
//				}
//			}
		}
		App::instance('CurrentCity', $current_city);
		View::share('current_city', $current_city && $current_city->id ? $current_city: null);
		View::share('current_city_id', $current_city && $current_city->id ? $current_city->id: 0);
		if($current_city && $current_city->id && $current_city->email){
			$header_email = $current_city->email;
			$footer_email = $current_city->email;
		} else {
			$header_email = Settings::get('header_email');
			$footer_email = Settings::get('footer_email');
		}

		View::share([
			'header_email'	=> $header_email,
			'footer_email'	=> $footer_email,
		]);

		return $next($request);
	}

}
