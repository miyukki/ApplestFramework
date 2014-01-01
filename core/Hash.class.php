<?php

class Hash {
	public static function create($name, $data) {
		return hash_hmac('sha256', $data, Config::get('salt').$name);
	}
	public static function random($name, $data) {
		return self::create($name, $data.rand());
	}
}