<?php

/**
 * cookies
 *
 * Example Usage:
 *
 *     Cookie::set('cookiename', 'cookievalue');
 *     $name = Cookie::get('yourname', 'Unnamed');
 *
 * @package     ApplestFramework
 */
class Cookie {
	/**
	 * Get value from cookie.
	 * クッキーから値を取得します
	 *
	 * @param	string		$key		name of cookie
	 * @param	mixed		$default	return this value if is it not found $key
	 * @return	string|null				cookie value or default value
	 */
	public static function get($key, $default = null) {
		if(isset($_COOKIE[$key])) {
			return $_COOKIE[$key];
		}

		return $default;
	}

	/**
	 * Set value to cookie.
	 * クッキーに値を代入します
	 *
	 * @param	string	$key		name of cookie
	 * @param	string	$value		return this value if is it not found $key
	 * @param	int		$expire		return this value if is it not found $key
	 * @param	string	$path		return this value if is it not found $key
	 */
	public static function set($key, $value, $expire = 2147483647, $path = '/', $domain = null) {
		setcookie($key, $value, $expire, $path, $domain);
	}

	/**
	 * Delete value from cookie.
	 * クッキーから値を消去します
	 *
	 * @param	string	$key		name of cookie
	 * @param	string	$path		return this value if is it not found $key
	 */
	public static function delete($key, $path = '/') {
		$this->setCookie($key, '', 0, $path);
	}
}