<?php namespace Fanky\Auth;

use Illuminate\Http\Response;
use Request;
use Session;
use Cookie;
use App\User;

class Auth
{
	protected static $user;

	public static function init()
	{
		if (self::logedIn()) return true;
		$user = Session::get('auth_user');
		if ($user) {
			self::$user = $user;
			return true;
		}

		return self::cookieAuth();
	}

	public static function cookieAuth()
	{
		$user_id = Request::cookie('user_id');
		$remember_token = Request::cookie('remember_token');
		
		if ($user_id && $remember_token) {
			self::$user = User::where('id', $user_id)->where('remember_token', $remember_token)->first();
		}

		if (self::logedIn()) {
			Session::put('auth_user', self::$user);
			return true;
		}

		return false;
	}

	public static function login($username, $password, $remember = false)
	{
		$password = md5(md5($password));
		
		self::$user = User::where('username', $username)->where('password', $password)->first();
		
		if ($remember) {
			self::cookieRemember();
		}

		if (self::logedIn()) {
			Session::put('auth_user', self::$user);
			return true;
		}
		
		return false;
	}

	public static function forceLogin($user, $remember = false)
	{
		self::$user = $user;

		if ($remember) {
			self::cookieRemember();
		}

		if (self::logedIn()) {
			Session::put('auth_user', self::$user);
			return true;
		}
		
		return false;
	}

	public static function logout()
	{
		self::$user = null;
		self::cookieForgot();
		Session::forget('auth_user');
	}

	protected static function cookieRemember()
	{
		if (!self::logedIn()) {
			return;
		}

		$response = new Response;

		$cookie1 = Cookie::forever('user_id', self::$user->id);
		$cookie2 = Cookie::forever('remember_token', self::$user->remember_token);

		$response->withCookie($cookie1, $cookie2);
	}

	protected static function cookieForgot()
	{
		$response = new Response;

		$cookie1 = Cookie::forget('user_id');
		$cookie2 = Cookie::forget('remember_token');

		$response->withCookie($cookie1, $cookie2);
	}

	public static function logedIn()
	{
		return (bool)(self::$user && self::$user->id);
	}

	public static function user()
	{
		return self::$user;
	}
}
