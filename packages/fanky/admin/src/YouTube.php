<?php namespace Fanky\Admin;

class YouTube {

	public static function src($id)
	{
		return 'http://www.youtube.com/embed/' . $id;
	}

	public static function url($id)
	{
		return 'http://www.youtube.com/watch?v=' . $id;
	}

	public static function thumb($id, $type = 0)
	{
		return 'https://i.ytimg.com/vi/' . $id . '/' . $type . '.jpg';
	}

	public static function getId($url){
		if (!strpos($url, "#!v=") === false) {
			$url = str_replace('#!v=', '?v=', $url);
		}
		parse_str(parse_url($url, PHP_URL_QUERY));
		if (isset($v)) {
			return $v;
		} else {
			return substr($url, strrpos($url, '/') + 1, 11);
		}
	}
}
