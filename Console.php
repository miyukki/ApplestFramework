<?php

require_once(dirname(__FILE__).'/Core.php');

class ConsoleController extends Controller {
	public function exec() {
		while(1){
			echo '>>> ';
			$a = eval(trim(fgets(STDIN)).';');
			var_dump($a);
			echo PHP_EOL;
		}
	}
}
dispatchAction('ConsoleController');