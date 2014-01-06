<?php

/**
 * Unit test core class.
 * This test class is inspired by lime of Symfony of a PHP framework.
 *
 * @package    ApplestFramework
 * @author     Yusei Yamanaka<info@applest.net>
 * @version    $id$
 */
class Test {
	const EPSILON = 0.0000000001;
	private static $count = 0;
	private static $pass_count = 0;
	private static $fail_count = 0;

	public static function ok() {}
	/**
	 * 
	 * 
	 * @param $exp1 needle
	 * @param $exp2 expected value
	 */
	public static function is($exp1, $exp2, $title) {
		if(is_object($exp1) || is_object($exp2)) {
			$result = $exp1 === $exp2;
		}else if(is_float($exp1) && is_float($exp2)) {
			$result = abs($exp1 - $exp2) < self::EPSILON;
		}else{
			$result = $exp1 == $exp2;
		}
		static::show_result($result, $title);
		if(!$result) {
			static::show_error(sprintf('got: %s, expected: %s', var_export($exp1, true), var_export($exp2, true)));
		}
		return $result;
	}

	public static function exp_ok($exp, $title) {
		$result = (bool)$exp;
		static::show_result($result, $title);
		return $result;
	}

	public static function is_fail() {
		return static::$fail_count > 0;
	}
	private static function show_result($result, $title) {
		if($result) {
			static::$pass_count++;
			Console::println('#' . ++static::$count . ' Pass - ' . $title, Console::GREEN);
		}else{
			static::$fail_count++;
			Console::println('#' . ++static::$count . ' Fail - ' . $title, Console::RED, Console::BOLD);
		}
	}
	private static function show_error($message) {
		Console::println(str_repeat(' ', strlen(static::$count) + 2) . $message);
	}
}