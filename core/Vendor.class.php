<?php

class Vendor {
	public static function load($file) {
		require_once(Config::get('path.vendor').'/'.$file);
	}
}