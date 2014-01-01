<?php

class Session {
	/*
	 * セッション保存ディレクトリへのパス
	 */
	private static $path;

	/*
	 * セッション参照時
	 */
	private static $salt;

	private static $name;

	private static $data = null;

	/*
	 * ブラウザに保存されるクッキーの名前
	 */
	private static $cookie_name = 'APPLEST_SESSION';

	private static function initialize() {
		self::$salt = Config::get('salt');
		self::$path = Config::get('session.path');
		self::$name = Cookie::get(Config::get('cookie_name', self::$cookie_name));
		// セッションが作られていなかった場合 || 存在しない場合
		if(is_null(self::$name) || !file_exists(self::get_filepath())) {
			self::$data = array();
			self::$name = self::create_hash();
			Cookie::set(Config::get('cookie_name', self::$cookie_name), self::$name);
			return;
		}
		self::$data = unserialize(file_get_contents(self::get_filepath()));
	}

	private static function finalize() {
		file_put_contents(self::get_filepath(), serialize(self::$data));
	}

	private static function get_filepath() {
		return self::$path . '/' . preg_replace('/[^0-9A-z]/', '', self::$name);
	}

	private static function create_hash() {
		mt_srand(crc32(self::$salt));
		return sha1(self::$salt . mt_rand() . date('U') . microtime() . Input::ip());
	}

	public static function get($key, $default = null) {
		if(is_null(self::$data)) self::initialize();
		if(isset(self::$data[$key])) return self::$data[$key];
		return $default;
	}

	public static function set($key, $value) {
		if(is_null(self::$data)) self::initialize();
		self::$data[$key] = $value;
		self::finalize();
	}
}