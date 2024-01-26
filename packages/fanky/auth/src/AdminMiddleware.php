<?php
namespace Fanky\Auth;

use Auth;
use Closure;

class AdminMiddleware
{

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Auth::init();

        if (Auth::user()->isAdmin) {
            return $next($request);
        }
        return redirect(route('auth') . '?redirect=' . \Request::decodedPath());
    }

}
