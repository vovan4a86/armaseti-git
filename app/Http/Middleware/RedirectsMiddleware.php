<?php namespace App\Http\Middleware;

use SiteHelper;
use Closure;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Redirect;
use Response;

class RedirectsMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle(\Illuminate\Http\Request $request, Closure $next) {
		$url = str_replace($request->root(), '', $request->url());
		//check_regions
		$prefix = '';
//		$cities = City::pluck('alias')->all();
		$cities = getCityAliases();
		$path = explode('/', $request->decodedPath());
		if(count($path)){
			$first_alias = array_get($path,0 );

			if(in_array($first_alias, $cities)){
				$prefix .= '/' . $first_alias;
				$url = str_replace('/' . $first_alias, '', $url);
			}
		}
		$redirect = SiteHelper::getRedirects($url);
		if ($redirect) {
			return Response::redirectTo($prefix . $redirect->to, $redirect->code);
		} else {
			return $next($request);
		}
	}

}
