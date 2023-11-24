<?php namespace Fanky\Admin;
use Fanky\Admin\Models\Setting;
use DB;

class Settings
{
	protected static $_data = null;

	public static function get($code, $default= null) {
		if (!self::$_data) {
			
			$data = DB::table('settings')->select('code', 'value', 'type', 'group_id')->get();
			foreach ($data as $item) {
				self::$_data[$item->code]['code'] = $item->code;
				self::$_data[$item->code]['value'] = $item->value;
				self::$_data[$item->code]['type'] = $item->type;
				self::$_data[$item->code]['group_id'] = $item->group_id;
			}
		}
		if (!isset(self::$_data[$code])) {
			return $default;
		}
		
		switch (self::$_data[$code]['type']) {
			case 4:
			case 5:
			case 6:
			case 7:
				$json = json_decode(self::$_data[$code]['value'], true);
				return is_array($json) ? $json : array();
				break;
			
			default:
				return self::$_data[$code]['value'];
				break;
		}
		
	}

	public static function fileSrc($value)
	{
		return Setting::UPLOAD_URL . $value;
	}
}
