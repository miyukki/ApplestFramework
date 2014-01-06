<?php
require(dirname(__FILE__).'/../Core.php');

class TestController extends Controller {
	public function exec() {
		$hello = 'world';
		if($hello !== 'world!') {
			exit(1);
		}
	}
}
dispatchAction('TestController');
