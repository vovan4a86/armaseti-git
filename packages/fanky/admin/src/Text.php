<?php namespace Fanky\Admin;

class Text {

	/**
	 * Translitirate text from ru to en
	 *
	 * @param string $string
	 * @param boolean $with_dash
	 *
	 * @return string
	 */
	public static function translit($string, $with_dash = true) {
		$tbl = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'g', 'з' => 'z',
			'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
			'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'ы' => 'i', 'э' => 'e', 'А' => 'A',
			'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ж' => 'G', 'З' => 'Z', 'И' => 'I',
			'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
			'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Ы' => 'I', 'Э' => 'E', 'ё' => "yo", 'х' => "h",
			'ц' => "ts", 'ч' => "ch", 'ш' => "sh", 'щ' => "shch", 'ъ' => "", 'ь' => "", 'ю' => "yu", 'я' => "ya",
			'Ё' => "YO", 'Х' => "H", 'Ц' => "TS", 'Ч' => "CH", 'Ш' => "SH", 'Щ' => "SHCH", 'Ъ' => "", 'Ь' => "",
			'Ю' => "YU", 'Я' => "YA", " " => "_", "-" => "_"
		);
		$text = strtr(trim($string), $tbl);
		$text = preg_replace('/[\W\s]+/', '', $text);
		$text = preg_replace('/\_+/', '_', $text);
		if($with_dash){
			$text = str_replace('_', '-', $text);
		}

		return strtolower($text);
	}

	public static function phone($number)
	{
		$pattern = array(
			'/^(\\+7|8)([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/',
			'/^([0-9]{3})([0-9]{2})([0-9]{2})$/'
		);
		$replacement = array('$1-$2-$3-$4-$5', '$1-$2-$3');
		return preg_replace($pattern, $replacement, $number);
	}

	// Склонение числительных
	public static function numberOf($numberof, $value, $suffix)
	{
	    // не будем склонять отрицательные числа
	    $numberof = abs($numberof);
	    $keys = array(2, 0, 1, 1, 1, 2);
	    $mod = $numberof % 100;
	    $suffix_key = $mod > 4 && $mod < 20 ? 2 : $keys[min($mod%10, 5)];
	    
	    return $value . $suffix[$suffix_key];
	}
}
