<?php

class Util {
	public static function is_hash(&$array) {
		reset($array);
		list($k) = each($array);
		return $k !== 0;
	}
	public static function get_unix_time() {
		return date('U');
	}
	public static function extension_remove($name) {
		return preg_replace('/\..+$/', '', $name);
	}
	public static function convert_action_name($name) {
		return preg_replace('/ /', '', ucwords(preg_replace('/_/', ' ', $name)));
	}
	public static function ends_with($haystack, $needle){
		$length = (strlen($haystack) - strlen($needle));
		if($length < 0) return FALSE;
		return strpos($haystack, $needle, $length) !== FALSE;
	}

	public static function convert_snake_case($value)
	{
		$value = strtolower(preg_replace('/([a-z])([A-Z])/', "$1_$2", $value));
		return $value;
	}

	public static function tableize($model_name) {
		$name = self::convert_snake_case($model_name);
		$names = explode('_', $name);
		$names[count($names)-1] = self::pluralize($names[count($names)-1]);
		$name = implode('_', $names);
		return $name;
	}
	public static function untableize($name) {
		$names = explode('_', $name);
		foreach ($names as &$part) {
			$part = ucfirst($part);
		}
		$name = implode('', $names);
		return $name;
	}
	public static function pluralize($singular) {
		$dictionary = array(
			'child'      => 'children',
			'crux'       => 'cruces',
			'foot'       => 'feet',
			'knife'      => 'knives',
			'leaf'       => 'leaves',
			'louse'      => 'lice',
			'man'        => 'men',
			'medium'     => 'media',
			'mouse'      => 'mice',
			'oasis'      => 'oases',
			'person'     => 'people',
			'phenomenon' => 'phenomena',
			'seaman'     => 'seamen',
			'snowman'    => 'snowmen',
			'tooth'      => 'teeth',
			'woman'      => 'women',
		);
		$plural = "";
		if (array_key_exists($singular, $dictionary)) {
			$plural = $dictionary[$singular];
		} elseif (preg_match('/(s|x|sh|ch|o)$/', $singular)) {
			$plural = preg_replace('/(s|x|sh|ch|o)$/', '$1s', $singular);
		} elseif (preg_match('/y$/', $singular)) {
			$plural = preg_replace('/y$/', 'ies', $singular);
		} else {
			$plural = $singular . "s";
		}
		return $plural;
	}
}