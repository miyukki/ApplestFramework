<?php

class Route {

	const T_RUN = 10;

	const T_FILE = 20;

	const T_TEMPLATE = 30;

	const T_OUTPUT = 40;

	const T_METHOD = 100;

	public $type;

	public function __construct($type) {
		$this->type = $type;
	}

	public $runs;
	public static function method($runs) {
		$r = new self(self::T_METHOD);
		$r->runs = $runs;
		return $r;
	}
	public function get_run() {
		foreach ($this->runs as $method => $run) {
			if(Input::method() === $method) {
				return $run;
			}
		}
	}

	public $target;
	public $controller_name;
	public $controller_method;
	public static function run($target) {
		$r = new self(self::T_RUN);
		$r->target = $target;
		$target = explode('#', $target);
		$r->controller_name = $target[0];
		$r->controller_method = $target[1];
		return $r;
	}

	public $file_path;
	public static function file($file_path) {
		$r = new self(self::T_FILE, $file_path);
		$r->file_path = $file_path;
		return $r;
	}
	
	public static function template($file_path) {
		$r = new self(self::T_TEMPLATE, $file_path);
		$r->file_path = $file_path;
		return $r;
	}

	public $output_str;
	public static function output($output_str) {
		$r = new self(self::T_OUTPUT, $output_str);
		$r->output_str = $output_str;
		return $r;
	}
}