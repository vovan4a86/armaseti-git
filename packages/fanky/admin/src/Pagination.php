<?php namespace Fanky\Admin;
use Request;

class Pagination {

	protected static $_obj;
	protected static $_page;
	protected static $_on_page = 5;
	protected static $_items_count;
	protected static $_pages_count;

	public static function init($obj, $on_page = null)
	{
		self::$_obj = $obj;
		if ($on_page) self::$_on_page = $on_page;
		self::$_items_count = self::$_obj->count();
		
		self::$_pages_count = ceil(self::$_items_count / self::$_on_page);
		self::$_page = max(1, min(self::$_pages_count, Request::input('p', 1)));

		$start = (self::$_page - 1) * self::$_on_page;
		return $obj->skip($start)->take(self::$_on_page);
	}

	public static function apply()
	{
		$start = (self::$_page - 1) * self::$_on_page;
		return self::$_obj->skip($start)->take(self::$_on_page);
	}

	public static function render($view = 'block.pagination')
	{
		if (!self::$_items_count || self::$_pages_count <= 1) return null;
		return view($view, [
			'url' => Request::url(),
			'curent_page' => self::$_page,
			'items_count' => self::$_items_count,
			'pages_count' => self::$_pages_count,
		])->render();
	}

	public static function get_count_items($format = true)
	{
		return $format ? number_format(self::$_items_count, 0, ',', ' ') : self::$_items_count;
	}

	public static function query($arr, $use_get = true)
	{
		if ($use_get) $arr = array_merge($_GET, $arr);
		$query_arr = [];
		foreach ($arr as $key => $value) {
			if ($value === false) continue;
			$query_arr[] = $key . '=' . $value;
		}
		return empty($query_arr) ? null : '?' . implode('&', $query_arr);
	}
}
