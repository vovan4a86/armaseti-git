<?php namespace Fanky\Admin;

class Date {

	public static $replace1 = [
		// month
		'january' => 'январь',
		'february' => 'февраль',
		'march' => 'март',
		'april' => 'апрель',
		'may' => 'май',
		'june' => 'июнь',
		'july' => 'июль',
		'august' => 'август',
		'september' => 'сентябрь',
		'october' => 'октябрь',
		'november' => 'ноябрь',
		'december' => 'декабрь',
		// week
		'monday' => 'понедельник',
		'tuesday' => 'вторник',
		'wednesday' => 'среда',
		'thursday' => 'четверг',
		'friday' => 'пятница',
		'saturday' => 'суббота',
		'sunday' => 'воскресенье',
	];

	public static $replace2 = [
		// month
		'january' => 'января',
		'february' => 'февраля',
		'march' => 'марта',
		'april' => 'апреля',
		'may' => 'мая',
		'june' => 'июня',
		'july' => 'июля',
		'august' => 'августа',
		'september' => 'сентября',
		'october' => 'октября',
		'november' => 'ноября',
		'december' => 'декабря',
		// week
		'monday' => 'понедельник',
		'tuesday' => 'вторник',
		'wednesday' => 'среда',
		'thursday' => 'четверг',
		'friday' => 'пятница',
		'saturday' => 'суббота',
		'sunday' => 'воскресенье',
	];

	public static function date($format, $time = null, $type = 1)
	{
		if (is_string($time)) $time = strtotime($time);
		$date = $time === null ? date($format) : date($format, $time);
		if ($type == 1) {
			$date = str_ireplace(array_keys(self::$replace2), array_values(self::$replace2), $date);
		}
		if ($type == 2) {
			$date = str_ireplace(array_keys(self::$replace2), array_values(self::$replace2), $date);
		}
		return $date;
	}

	public static function range($time1, $time2 = null)
	{
		if ($time2 === null) $time2 = time();
		$range = abs($time1 - $time2);
		$days = floor($range / 86400);
		$hours = $range % 86400;
		$hours = floor($hours / 3600);
		$minutes = $range % 3600;
		$minutes = floor($minutes / 60);
		$secunds = $range % 60;

		$arr = [];
		if ($days) $arr[] = $days. ' д.';
		if ($hours) $arr[] = $hours. ' ч.';
		if ($minutes) $arr[] = $minutes. ' мин.';
		return implode(' ', $arr);
	}
}
