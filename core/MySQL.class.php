<?php

/**
 * MySQLクラス
 * pdoを利用しMySQLを前提とした関数を提供
 *
 * @packege ApplestFramework
 * @author Yusei Yamanaka<info@applest.net>
 */
class MySQL {

	private static $instance = null;

	private function __construct() {}
	public static function getInstance() {
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	private $pdo;

	public function connect($source, $user, $password, $charset = 'utf8') {
		$this->pdo = new PDO($source, $user, $password);
		if($charset)
		{
			$this->pdo->query("SET NAMES $charset");
		}
	}

	public function exec($query, $arrval = array(), $get_data = false, $get_last_id = false) {
		Log::verbose('MySQL Query:  '.var_export($query, true));
		Log::verbose('MySQL Values: '.var_export($arrval, true));

		$stmt = $this->pdo->prepare($query);
		$result = $stmt->execute($arrval);

		if(!$result && Config::get('debug', false))
		{
			var_dump($query, $arrval);
			var_dump($this->pdo->errorInfo());
			throw new Exception('SQL command is not ended.');
		}

		if($get_last_id)
		{
			return $this->pdo->lastInsertId();
		}

		if($get_data === false)
		{
			return $stmt;
		}

		$returndata = array();
		while($data = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$returndata[] = $data;
		}
		return $returndata;
	}
}