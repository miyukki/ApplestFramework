<?php

class Test {
	private static $count = 1;

	public static function exp_ok($exp, $title) {
		$result = (bool)$exp;
		static::show_result($result, $title);
		return $result;
	}

	private static function show_result($result, $title) {
		if($result) {
			Console::println('#' . static::$count++ . ' Success ' . $title, Console::GREEN);
		}else{
			Console::println('#' . static::$count++ . ' Fail ' . $title, Console::RED, Console::BOLD);
		}
	}
}