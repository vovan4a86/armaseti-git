<?php namespace Fanky\Auth\Controllers;

use App\Http\Controllers\Controller;
use Request;
use App\User;
use Auth;
use Cookie;

class AuthController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		//$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index() {
		Auth::logout();
		if (Request::isMethod('post')) {
			$username = Request::input('username');
			$password = Request::input('password');
			$remember = (bool)Request::input('remember');
			if (!$username || !$password) {
				return redirect('auth')->withInput(['error' => 'Не заполнены обязательные поля!']);
			}
			if (Auth::login($username, $password, $remember)) {
				if(Request::input('redirect')){
					$url = url(Request::input('redirect'));
				} else {
					$url = route('admin');
				}
				if ($remember) {
					$cookie1 = Cookie::forever('user_id', Auth::user()->id);
					$cookie2 = Cookie::forever('remember_token', Auth::user()->remember_token);

					return redirect($url)->withCookie($cookie1)->withCookie($cookie2);
				}

				return redirect($url);
			} else {
				return redirect('auth')->withInput(['error' => 'Не верные имя пользователя или пароль!']);
			}
		} else {
			$error = Request::input('error');
			$cookie1 = Cookie::forget('lc_site_id');
			$cookie2 = Cookie::forget('lc_remember_token');
			$response = new \Illuminate\Http\Response(view('auth::login', ['error' => $error]));

			return $response->withCookie($cookie1)->withCookie($cookie2);
		}
	}

}
