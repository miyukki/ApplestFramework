<?php

/**
 * Controller
 * 
 * @package ApplestFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 */
class Controller {
	public function loadLogic($logic_name) {
		require_once(Config::get('path.logic').'/'.$logic_name.'.class.php');
	}

	public function before() {}

	public function after() {}
}
class APIController extends Controller {
	
}