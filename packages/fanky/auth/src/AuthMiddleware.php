<?php namespace Fanky\Auth;

use Auth;
use Closure;

class AuthMiddleware {

	/**
	 * Run the request filter.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		Auth::init();

		if (!Auth::logedIn() || Auth::user()->status != 1)
		{
			return redirect(route('auth'). '?redirect=' . \Request::decodedPath());
		}

		return $next($request);
	}

}