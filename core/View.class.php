<?php

class View {
	const MODE_DEFAULT = 0;
	const MODE_API = 10;

	private static $buffer = '';
	private static $mode = self::MODE_DEFAULT;

	public static function show($file_name, $data = null) {
		Log::warn('DEPRECATED: View::show() is deprecated, should use View::template().');
		self::template($file_name, $data);
	}
	public static function template($file_name, $data = null) {
		if(is_null($data)) $data = array();
		$clean_room = function($__file_name, array $__data, &$__buffer) {
			extract($__data, EXTR_REFS);

			// Capture the view output
			ob_start();

			try {
				// Load the view within the current scope
				include $__file_name;
			} catch (Exception $e) {
				// Delete the output buffer
				ob_end_clean();

				// Re-throw the exception
				throw $e;
			}

			// Get the captured output and close the buffer
			$__buffer .= ob_get_clean();
		};
		return $clean_room(Config::get('path.template').'/'.$file_name, $data, self::$buffer);
	}
	// public static function include($)
	public static function mode($mode) {
		// JSON XML
		self::$mode = $mode;
	}
	public static function api($data, $result = true) {
		if(!isset($data['result'])) {
			$data['result'] = $result;
		}

		self::$buffer .=  json_encode($data);
	}
	public static function getBuffer() {
		$buffer = self::$buffer;
		self::$buffer = '';
		return $buffer;
	}
}