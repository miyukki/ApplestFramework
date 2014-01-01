<?php

/**
 * route
 *
 * Example Usage:
 *
 *     Cookie::set('cookiename', 'cookievalue');
 *     $name = Cookie::get('yourname', 'Unnamed');
 *
 * @package     ApplestFramework
 */
class Router {


	/**
	 * @var  array  from route file
	 */
	private static $routes;

	public static function get($key = null) {
		// 初回のみルート設定を読み込み
		if(is_null(self::$routes)) {
			self::$routes = require(ROUTE_FILE);
		}

		// これいらない、500の時に使う、どうするか
		if(!is_null($key)) {
			return self::$routes[$key];
		}

		$last_path = '';
		$last_route = null;
		foreach (self::$routes as $path => $route) {
			// 正規表現用の置換
			// /users/:id/detail => \/users\/[^\/]\/detail
			$regpath = preg_replace('/:[A-z0-9]+/', '[^/]+', $path);
			$regpath = str_replace('/', '\/', $regpath);
			// パスは文字が確定しているほど優先度が高い
			if(preg_match('/^'.$regpath.'$/', Input::path()) === 1 && ($last_path === '' || substr_count($last_path, ':') > substr_count($path, ':'))) {
				// T_METHOD の時でメソッドが一致したら続行
				if($route->type === Route::T_METHOD) {
					$last_path = $path;
					$last_route = $route->get_run();
					continue;
				}
				$last_path = $path;
				$last_route = $route;
			}
		}

		if($last_path === '' || is_null($last_route)) {
			return self::$routes['404'];
		}

//こっからまるごとdispachに移したい

		// URLパラメータの設定用
		Input::setParams($last_path);
		return $last_route;
	}
}