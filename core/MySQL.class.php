<?php

/**
 * MySQLクラス
 * pdoを利用しMySQLを前提とした関数を提供
 *
 * @packege MiyukkiFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 * @version $id$
 */
class MySQL {
	var $pdo;

	private static $instance = null;

	private function __construct() {}

	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function connect($source, $user, $password, $charset = 'utf8') {
		$this->pdo = new PDO($source, $user, $password);
		if($charset) {
			$this->pdo->query("SET NAMES $charset");
		}
	}

	public function exec($query, $arrval = array(), $returndata = false, $returnlastid = false) {
		// var_dump($query, $arrval);
		Log::verbose('MySQL Query:  'var_export($query, true));
		Log::verbose('MySQL Values: 'var_export($arrval, true));
		$stmt = $this->pdo->prepare($query);
		$q = $stmt->execute($arrval);
		if(!$q) {
			if(Config::get('debug', false)) {
				var_dump($query, $arrval);
				var_dump($this->pdo->errorInfo());
				throw new Exception('SQL command is not ended.');
			}
		}
		if($returnlastid) return $this->pdo->lastInsertId();
		if(!$returndata) return $stmt;
		$dat = array();
		while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$dat[] = $data;
		}
		return $dat;
	}
}