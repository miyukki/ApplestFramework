<?php

class Environment {
	const NONE = 0;
	const DEVELOPMENT = 10;
	const STAGING = 20;
	const TEST = 30;
	const PRODUCTION = 100;

	protected static $type = null;

	public static function get() {
		if(is_null(static::$type)) {
			static::$type = self::NONE;
			if(file_exists(ENVIRONMENT_DIR.'/development')) {
				static::$type = self::DEVELOPMENT;
			}
			if(file_exists(ENVIRONMENT_DIR.'/staging')) {
				static::$type = self::STAGING;
			}
			if(file_exists(ENVIRONMENT_DIR.'/test')) {
				static::$type = self::TEST;
			}
			if(file_exists(ENVIRONMENT_DIR.'/production')) {
				static::$type = self::PRODUCTION;
			}
		}
		return self::$type;
	}
}