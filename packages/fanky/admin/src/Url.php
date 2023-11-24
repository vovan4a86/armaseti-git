<?php namespace Fanky\Admin;

class Url {

	public static function query($arr, $use_get = true)
	{
		if ($use_get) $arr = array_merge($_GET, $arr);
		$query_arr = [];
		foreach ($arr as $key => $value) {
			$query_arr[] = $key . '=' . $value;
		}
		return empty($query_arr) ? null : '?' . implode('&', $query_arr);
	}
}
