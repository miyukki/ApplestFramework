<?php

class Console {
	const NORMAL = 0;
	const BOLD = 1;
	const UNDERLINE = 4;
	const BLINK = 5;
	const REVERSE = 7;

	const BLACK = 30;
	const RED = 31;
	const GREEN = 32;
	const YELLOW = 33;
	const BLUE = 34;
	const PURPLE = 35;
	const AQUA = 36;
	const WHITE = 37;

	const BG_BLACK = 40;
	const BG_RED = 41;
	const BG_GREEN = 42;
	const BG_YELLOW = 43;
	const BG_BLUE = 44;
	const BG_PURPLE = 45;
	const BG_AQUA = 46;
	const BG_WHITE = 47;

	public static function println() {
		$args = func_get_args();
		$message = array_shift($args);
		echo pack('c',0x1B) . '['.implode($args, ';').'m' .$message;
		echo pack('c',0x1B) . '[0m'.PHP_EOL;
	}
}
