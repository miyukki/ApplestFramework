<?php

class Response {
	public static function redirect($url, $permanently = false) {
		if($permanently) {
			header('HTTP/1.1 301 Moved Permanently');
		}else{
			header('HTTP/1.1 302 Found');
		}
		if(strpos($url, '://') !== FALSE) {
			header('Location: '.$url);
			return;
		}
		header('Location: '.Config::get('base_url').$url);
	}
}