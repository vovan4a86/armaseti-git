<?php namespace Fanky\Admin;
use Image;
use Fanky\Admin\Settings;

class Thumb {

	private static $dir = '/thumbs';
	private static $postfix = '.thumb_';

	public static function url($url, $thumb)
	{
		$path_parts = pathinfo($url);
		return self::$dir . $path_parts['dirname'] . '/' . $path_parts['filename'] . self::$postfix . $thumb . '.' . $path_parts['extension'];
	}

	public static function make($url, $thumb, $size = null, $fit = null)
	{
		if (is_array($thumb)) {
			$result = 0;
			foreach ($thumb as $key => $value) {
				$params = explode('|', $value);
				if (self::make($url, $key, $params[0], array_get($params, 1, null))) $result++;
			}
			return $result;
		}
		if (!$size) return false;

		$sizes = explode('x', $size);
		$width = array_get($sizes, 0);
		$height = array_get($sizes, 1);

		$thumb_file = base_path('/public' . self::url($url, $thumb));
		$thumb_dir = pathinfo($thumb_file, PATHINFO_DIRNAME);
		if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0775, true);

		$image = Image::make(base_path('/public' . $url));
		if ($fit == 'fit') {
			$image->fit($width, $height);
		} else {
			$image->resize($width, $height, function ($constraint) {
			    $constraint->aspectRatio();
			    $constraint->upsize();
			});
		}
			
		$image->save($thumb_file, Settings::get('image_quality', 100));

		return true;
	}

	public static function delete($url)
	{
		$path_parts = pathinfo($url);
		$pattern = public_path(self::$dir . $path_parts['dirname'] . '/' . $path_parts['filename'] . self::$postfix . '*.' . $path_parts['extension']);
		if ($items = glob($pattern)) {
			foreach ($items as $item) {
				@unlink($item);
			}
		}
		return empty($items) ? null : count($items);
	}

	public static function get($url, $size, $fit = false, $thumb_key = null)
	{
		if (!$thumb_key) $thumb_key = $size.($fit ? '_fit' : '');
		$thumb_url = self::url($url, $thumb_key);

		if (file_exists(public_path($thumb_url))) return $thumb_url;

		if (self::make($url, $thumb_key, $size, $fit)) return $thumb_url;

		return null;
	}
}
