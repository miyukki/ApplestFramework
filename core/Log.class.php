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
	const VERBOSE = 0;
	const DEBUG   = 1;
	const INFO    = 2;
	const WARN    = 3;
	const ERROR   = 4;

	const DUMP    = 10;

	private static $delay_mode = true;
	private static $output_mode = false;

	private static $logs = array();

	private static $log_level = self::INFO;
	public static function level($log_level)
	{
		self::$log_level = $log_level;
	}

	public static function delay() {
		self::$delay_mode = false;
	}
	public static function output() {
		self::$output_mode = true;
	}

	public static function dump($object) {
		ob_start();
		$dump_str = ob_get_contents();
		ob_end_clean();
		self::write(self::DUMP, 'DUMP', $dump_str);
	}

	public static function verbose($log_text) {
		self::write(self::VERBOSE, 'VERBOSE', $log_text);
	}

	public static function debug($log_text) {
		self::write(self::DEBUG, 'DEBUG', $log_text);
	}

	public static function info($log_text) {
		self::write(self::INFO, 'INFO', $log_text);
	}

	public static function warn($log_text) {
		self::write(self::WARN, 'WARN', $log_text);
	}

	public static function error($log_text) {
		self::write(self::ERROR, 'ERROR', $log_text);
	}

	private static function write($log_level, $label, $log_text) {
		self::$logs[] = array($log_level, sprintf('[%s] %s %s', $label, date('Y:m:d H:i:s'), $log_text));
	}

	public static function finalize() {
		if(count(self::$logs) === 0) return;
		
		$log_str = '';
		foreach (self::$logs as $log)
		{
			if(self::$log_level > $log[0])
			{
				continue;
			}
			$log_str .= $log[1].PHP_EOL;
		}
		$log_name = date('Ymd').'.log';
		file_put_contents(Config::get('path.log').'/'.$log_name, $log_str, FILE_APPEND);
	}
}
