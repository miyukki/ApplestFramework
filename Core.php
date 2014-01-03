<?php
/**********************************************************************************************
	___                __          __   ______                                             __  
   /   |  ____  ____  / /__  _____/ /_ / ____/________ _____ ___  ___ _      ______  _____/ /__
  / /| | / __ \/ __ \/ / _ \/ ___/ __// /_  / ___/ __ `/ __ `__ \/ _ \ | /| / / __ \/ ___/ //_/
 / ___ |/ /_/ / /_/ / /  __(__  ) /_ / __/ / /  / /_/ / / / / / /  __/ |/ |/ / /_/ / /  / ,<   
/_/  |_/ .___/ .___/_/\___/____/\__//_/   /_/   \__,_/_/ /_/ /_/\___/|__/|__/\____/_/  /_/|_|  
	  /_/   /_/                                                                                
***********************************************************************************************/
/**
 * ApplestFramework - Go to the next generation of PHP.
 * 
 * @version		3.0
 * @package		ApplestFramework
 * @author		Yusei Yamanaka<info@applest.net>
 * @copyright	2013- Applest
 * @license		MIT License<http://opensource.org/licenses/mit-license.php>
 * @link		http://fw.applest.net/
 */

define('BASE_DIR', dirname(__FILE__));
define('ROUTE_FILE', BASE_DIR.'/Route.php');
define('ENVIRONMENT_DIR', BASE_DIR.'/environment');

require(BASE_DIR.'/core/View.class.php');
require(BASE_DIR.'/core/Event.class.php');
require(BASE_DIR.'/core/Vendor.class.php');
require(BASE_DIR.'/core/Redis.class.php');
require(BASE_DIR.'/core/Session.class.php');
require(BASE_DIR.'/core/Hash.class.php');
require(BASE_DIR.'/core/Validation.class.php');
require(BASE_DIR.'/core/Validator.class.php');
require(BASE_DIR.'/core/Model.class.php');
require(BASE_DIR.'/core/Type.class.php');
require(BASE_DIR.'/core/MySQL.class.php');
require(BASE_DIR.'/core/Util.class.php');
require(BASE_DIR.'/core/Input.class.php');
require(BASE_DIR.'/core/Response.class.php');
require(BASE_DIR.'/core/Cookie.class.php');
require(BASE_DIR.'/core/Config.class.php');
require(BASE_DIR.'/core/Route.class.php');
require(BASE_DIR.'/core/Router.class.php');
require(BASE_DIR.'/core/Environment.class.php');
require(BASE_DIR.'/core/Logic.class.php');
require(BASE_DIR.'/core/Controller.class.php');

switch (Environment::get()) {
	case Environment::PRODUCTION:
		define('CONFIG_FILE', BASE_DIR.'/Config.production.php');
		break;
	case Environment::TEST:
		define('CONFIG_FILE', BASE_DIR.'/Config.test.php');
		break;
	case Environment::STAGING:
		define('CONFIG_FILE', BASE_DIR.'/Config.staging.php');
		break;
	case Environment::DEVELOPMENT:
		define('CONFIG_FILE', BASE_DIR.'/Config.development.php');
		break;
	case Environment::NONE:
		define('CONFIG_FILE', BASE_DIR.'/Config.php');
		break;
}

/**
 * Run the action in the controller.
 * コントローラーの中のアクションを実行します
 *
 * @param	string	$controller_target
 */
function dispatchAction($controller_target = null) {
	$af = new ApplestFramework();
	$af->dispatchAction($controller_target);
}

/**
 * The core of the framework.
 * フレームワークのコア
 *
 * @package		ApplestFramework
 */
class ApplestFramework {
	private $controller_target;

	/**
	 * Called by spl_autoload_register.
	 * __autoloadにスタックするspl_autoload_registerにより呼ばれます
	 * 
	 * @param	string	$class	classname
	 */
	public static function autoloader($class) {
		if(!preg_match("/^_?[A-z0-9]+$/",$class)) return;
		if(Util::ends_with($class, 'Model')) {
			eval("class $class extends Model { static \$_name = '$class';}");
		}
		if(Util::ends_with($class, 'Type')) {
			$type_file = Config::get('path.type').'/'.$class.'.class.php';
			if(file_exists($type_file)) {
				include($type_file);
			}else{
				eval("class $class extends Type {}");
			}
		}
		if(Util::ends_with($class, 'Logic')) {
			include(Config::get('path.logic').'/'.$class.'.class.php');
		}
	}

	public function __construct() {
		mb_internal_encoding('utf-8');
		spl_autoload_register('ApplestFramework::autoloader', true, true);
		// var_dump(date_default_timezone_get());
		date_default_timezone_set('Asia/Tokyo');

		// デバッグ設定
		if(Config::get('debug', false)) {
			error_reporting(-1);
			ini_set('display_errors', 1);
		}else{
			error_reporting(0);
		}

		if(in_array('session', Config::get('use'))) {
			// $session_path = Config::get('session.path');
			// if(!$session_path) {
			// 	throw new Exception('No Define Session Path!');
			// }
			// Session::setPath($session_path);
		}

		// MySQL使用
		if(in_array('mysql', Config::get('use'))) {
			$db = MySQL::getInstance();
			$db->connect(sprintf('mysql:dbname=%s;host=%s', Config::get('mysql.name'), Config::get('mysql.host')), Config::get('mysql.user'), Config::get('mysql.password'));
		}
	}
	public function dispatchAction($controller_name  = null) {
		try {
			$this->dispatchAction2($controller_name);
		} catch (Exception $e) {
			if(Config::get('debug', false)) {
				throw $e;
			}else{
				// View::show(Config::get('path.public').'/'.$last_target, null);
			}
		}
	}

	private function dispatchAction2($controller_name) {
		if(!is_null($controller_name)) {
			$route = Route::run($controller_name.'#exec');
			$this->run($route);
			return;
		}

		$route = Router::get();

		if($route->type === Route::T_RUN) {
			$this->run($route);
			return;
		}

		// ターゲットが静的なファイルだった場合
		if($route->type === Route::T_FILE) {
			if(file_exists(Config::get('path.public').'/'.$route->file_path)) {
				View::show(Config::get('path.public').'/'.$route->file_path, null);
			}else{
				throw new Exception("Does not exists static file.");
			}
			return;
		}

		if($route->type === Route::T_TEMPLATE) {
			if(file_exists(Config::get('path.template').'/'.$route->file_path)) {
				View::show(Config::get('path.template').'/'.$route->file_path, null);
			}else{
				throw new Exception("Does not exists template file.");
			}
			return;
		}

		// ターゲットが出力だった場合
		if($route->type === Route::T_OUTPUT) {
			echo $route->output_str;
			return;
		}

		throw new Exception("Route Error!");
	}
	private function run($route) {
		if(!class_exists($route->controller_name)) {
			if(!file_exists(Config::get('path.controller').'/'.$route->controller_name.'.class.php')) {
				throw new Exception("Does not exists controller script or static file.");
			}
			require_once(Config::get('path.controller').'/'.$route->controller_name.'.class.php');
		}

		$controller = new $route->controller_name();

		// _BeforeAction
		$before_action = Config::get('path.action').'/_BeforeAction.class.php';
		if(file_exists($before_action) && $action->before_action) {
			require_once($before_action);
			$before_action = new _BeforeAction;
			if(method_exists($before_action, 'smarty_exec')) {
				list($smarty, $action->data) = $before_action->smarty_exec($this->getSmartyObject());
			}else{
				$action->data = $before_action->exec();
			}
		}

		// コントローラーファイルの実行前関数
		if(method_exists($controller, 'before')) {
			$controller->before();
		}

		// メイン
		if(method_exists($controller, $route->controller_method)) {
			$method = $route->controller_method;
			$controller->$method();
		}elseif(method_exists($controller, 'exec')) {
			$controller->exec();
		}else{
			throw new Exception("There is no method that can be run.", 1);
		}

		// コントローラーファイルの実行後関数
		if(method_exists($controller, 'after')) {
			$controller->after();
		}

		// _AfterAction
		$after_action = Config::get('path.action').'/_AfterAction.class.php';
		if(file_exists($after_action) && $action->after_action) {
			require_once($after_action);
			$after_action = new _BeforeAction;
			$after_action->exec();
		}
	}
}
