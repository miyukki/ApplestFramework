<?php

/**
 * Controller
 * 
 * @package ApplestFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 */
class Controller {
	public $data;
	public $before_action = true;
	public $after_action = true;
	public function loadLogic($logic_name) {
		require_once(Config::get('path.logic').'/'.$logic_name.'.class.php');
	}
}
class APIController extends Controller {
	
}