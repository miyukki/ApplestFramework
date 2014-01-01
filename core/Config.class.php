<?php

class Config {
	private static $config;

	public static function get($key, $default = null) {
		if(is_null(self::$config)) {
			self::$config = require(CONFIG_FILE);
		}
		// 連想配列モード
		if(strpos($key, '.') !== FALSE) {
			$assoc = self::$config;
			$assoc_keys = preg_split('/\./', $key);
			for($i=0; $i < count($assoc_keys)-1; $i++) {
				if(!isset($assoc[$assoc_keys[$i]])) return $default;
				$assoc = $assoc[$assoc_keys[$i]];
			}
			return isset($assoc[$assoc_keys[$i]])?$assoc[$assoc_keys[$i]]:$default;
		}
		return isset(self::$config[$key])?self::$config[$key]:$default;
	}
}