<?php

class Input {
	// GETメソッド用
	public static function get($key, $default = null) {
		if(isset($_GET[$key])) {
			return $_GET[$key];
		}

		return $default;
	}
	// POSTメソッド用
	public static function post($key, $default = null) {
		if(isset($_POST[$key])) {
			return $_POST[$key];
		}

		return $default;
	}

	protected static $php_input = null;
	protected static $put_param = null;
	protected static function hydrate(){
	    if (Input::method() == 'PUT' or Input::method() == 'PATCH' or Input::method() == 'DELETE') {
            static::$php_input === null and static::$php_input = file_get_contents('php://input');
            parse_str(static::$php_input, static::$put_param);
	    } else {
            static::$put_param = array();
	    }
    }
	public static function put($key = null, $default = null) {
		static::$put_param === null and static::hydrate();
		// var_dump(static::$put_param);
		if(isset($put_param[$key])) {
			return $put_param[$key];
		}

		return $default;
	}

	public static function file($key, $only = false, $default = null) {
		if(!isset($_FILES[$key])) {
			return $default;
		}

		$files = array();
		if(is_array($_FILES[$key]['error'])) {
			for($i = 0; $i < count($_FILES[$key]['error']); $i++) {
				if($_FILES[$key]['error'][$i] !== UPLOAD_ERR_OK) {
					continue;
				}

				$files[] = array(
					'name'     => $_FILES[$key]['name'][$i],
					'type'     => $_FILES[$key]['type'][$i],
					'tmp_name' => $_FILES[$key]['tmp_name'][$i],
					'error'    => $_FILES[$key]['error'][$i],
					'size'     => $_FILES[$key]['size'][$i],
				);
			}
		}else{
			if($_FILES[$key]['error'] === UPLOAD_ERR_OK) {
				$files[] = array(
					'name'     => $_FILES[$key]['name'],
					'type'     => $_FILES[$key]['type'],
					'tmp_name' => $_FILES[$key]['tmp_name'],
					'error'    => $_FILES[$key]['error'],
					'size'     => $_FILES[$key]['size'],
				);
			}
		}

		if(count($files) == 0) { 
			return $default;
		}
		if(count($files) == 1 || $only) {
			return $files[0];
		}
		return $files;
	}

	// URLパラメータ用
	private static $params = null;
	public static function setParams($path) {
		self::$params = array();

		preg_match_all('/:([A-z0-9]+)/', $path, $param_keys);

		$regpath = preg_replace('/:[A-z0-9]+/', '([^/]+)', $path);
		$regpath = str_replace('/', '\/', $regpath);
		preg_match('/^'.$regpath.'$/', Input::path(), $param_vals);
		// マッチの最初はマッチした全体が帰ってくる
		array_shift($param_vals);
		
		foreach ($param_keys[1] as $index => $param_key) {
			self::$params[$param_key] = $param_vals[$index];
		}
	}
	public static function param($key, $default = null) {
		if(isset(self::$params[$key])) {
			return self::$params[$key];
		}

		return $default;
	}
	private static $stdin = null;
	public static function body() {
		if(is_null(self::$stdin)) {
			self::$stdin = file_get_contents('php://input');
		}

		return self::$stdin;
	}
	public static function bodyJson() {
		return json_decode(self::body(), true);
	}
	public static function ip($default = null) {
		if(isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return $default;
	}
	public static function path() {

		// PATH_INFO環境変数がある場合(index.php/foobarを参照)
		$path_info = getenv('PATH_INFO');

		// PATH_INFO環境変数がない場合(index.php?/foobarを参照)
		if(!$path_info)
		{
			$queries = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($queries as $query)
			{
				if(strpos($query, '/') === 0)
				{
					$path_info = $query;
					break;
				}
			}
		}

		// それでも設定できない場合
		if(!$path_info)
		{
			Log::warn('パスが自動で判別できませんでした。')
			Log::warn('Cannot auto detected virtual path.')
			$path_info = '/';
		}
		
		return $path_info;
	}
	public static function method() {
		if(defined('STDIN')) return 'CLI';
		return $_SERVER['REQUEST_METHOD'];
	}
}