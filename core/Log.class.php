<?php

/**
 * Log
 *
 * ログを管理するクラスです
 * 
 * @package ApplestFramework
 * @author miyukki<yusei1128@gmail.com>
 */
class Log {
	private static $delay_mode = true;
	private static $output_mode = false;

	private static $logs = array();

	public static function delay() {
		self::$delay_mode = false;
	}
	public static function output() {
		self::$output_mode = true;
	}

	public static function dump($object) {
		ob_start();
		var_dump($object);
		$dump_str = ob_get_contents();
		ob_end_clean();
		self::write($dump_str);
	}

	public static function info($log_text) {
		self::write('INFO', $log_text);
	}

	public static function warn($log_text) {
		self::write('WARN', $log_text);
	}

	public static function error($log_text) {
		self::write('ERROR', $log_text);
	}

	private static function write($level, $log_text) {
		// self::$logs[] = '['.$level.'] '.date('Y:m:d H:i:s').' '.$log_text;
		self::$logs[] = sprintf('[%s] %s %s', $level, date('Y:m:d H:i:s'), $log_text);
	}

	public static function finalize() {
		if(count(self::$logs) === 0) return;
		
		$log_str = '';
		foreach (self::$logs as $log) {
			$log_str .= $log.PHP_EOL;
		}
		$log_name = date('Ymd').'.log';
		file_put_contents(Config::get('path.log').'/'.$log_name, $log_str, FILE_APPEND);
	}
}
