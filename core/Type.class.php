<?php

/**
 * Type
 *
 * DBのカラムをデータとして持ち,値の正規判定もする
 *
 * @package ApplestFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 */
class Type {
	private $_name;

	private $_data = array();

	private $_throw_exception = true;

	protected $_enable_hibernate = true;


	/*
	 * セッション内での二重問い合わせ防止用のキャッシュ
	 * join問い合わせなどで使用
	 */
	protected $_object_cache = array();

	protected $_properties = array();

	private $_default_properties = array(
		'id' => array(
			'type' => 'int',
		),
		'created_at' => array(
			'type' => 'int',
		),
		'updated_at' => array(
			'type' => 'int',
		),
		'deleted_at' => array(
			'type' => 'int',
		),
	);

	protected function getProperty() {
		return array();
	}

	protected $_output_keys = array();
	
	public function __construct($data = null) {
		$this->_properties = array_merge($this->_default_properties, $this->_properties, $this->getProperty());

		// データが有る場合はデータを代入
		if(!is_null($data))
		{
			$this->_data = $data;
		}
	}

	public function setName($name) {
		$this->_name = $name;
	}
	public function getName() {
		return $this->_name;
	}

	/**
	 * __setマジックメソッド
	 * データにキーと値を代入
	 * 値のバリデーションは後で
	 */
	public function __set($key, $value) {
		// get type
		if(array_key_exists($key, $this->_properties) && array_key_exists('join', $this->_properties[$key])) {
			$join_key = $this->_properties[$key]['join'];
			$this->_data[$key . '_' . $join_key] = $value->$join_key;
			// object cache
 			$this->_object_cache[$key] = $value;
			return;
		}
		// get array
		// ここで配列を保存してしまうとsaveが呼ばれていないにもかかわらずトランザクションが発生するのでここではキャッシュにいれてsave時に保存する
		if(array_key_exists($key, $this->_properties) && array_key_exists('type', $this->_properties[$key]) && $this->_properties[$key]['type'] === 'array') {
			$this->_object_cache[$key] = $value;
			return;
		}
		$this->_data[$key] = $value;
	}

	/**
	 * __callマジックメソッド
	 * もうすぐ無くなる予定
	 */
	public function __call($name, $arguments) {
		// DEPRECATED
		if(preg_match('/^get[A-Z]/', $name))
		{
			$colum = Util::convert_snake_case(substr($name, 3)).'_id';
			if(array_key_exists($colum, $this->_data))
			{
				if(isset($arguments[0])) {
					$model_name = $arguments[0];
				}
				else
				{
					$model_name = explode('_', $colum); // source, user. id
					$model_name = $model_name[count($model_name)-2]; // user

					$model_name = Util::untableize($model_name).'Model'; // UserModel
				}
				return $model_name::find_by_id($this->_data[$colum]);
			}
		}
	}

	/**
	 * __getマジックメソッド
	 * データからキーを取得
	 * 
	 * @param string $key 取得するキー
	 */
	public function __get($key) {
		// get type
		if(array_key_exists($key, $this->_properties) && array_key_exists('join', $this->_properties[$key])) {
			if(!array_key_exists($key, $this->_object_cache)) {
				// ex. id
				$join_key = $this->_properties[$key]['join'];
				// ex. find_by_id
				$model_method = 'find_by_'.$join_key;
				// ex. UserType => UserModel
				$model_name = substr($this->_properties[$key]['type'], 0, -4).'Model';
				// ex. UserModel::find_by_id( $this->user_id )
				$this->_object_cache[$key] = $model_name::$model_method($this->_data[$key . '_' . $join_key]);
			}
			return $this->_object_cache[$key];
		}
		// get array
		if(array_key_exists($key, $this->_properties) && array_key_exists('type', $this->_properties[$key]) && $this->_properties[$key]['type'] === 'array') {
			if(!array_key_exists($key, $this->_object_cache)) {
				$this->_object_cache[$key] = $this->getArrayObject($this->id, $key);
			}
			return $this->_object_cache[$key];
		}

		if(!array_key_exists($key, $this->_data)) {
			return NULL;
		}

		if(is_array($this->_properties) && array_key_exists($key, $this->_properties) && array_key_exists('type', $this->_properties[$key])) {
			switch ($this->_properties[$key]['type']) {
				case 'int':
					$this->_data[$key] = (int)$this->_data[$key];
					break;
			}
		}
		return $this->_data[$key];
	}

	/* 行データ */
	public function getData() {
		return $this->_data;
	}

	public function output() {
		$ret = array();
		foreach ($this->_data as $key => $value) {
			if(in_array($key, $this->_output_keys)) {
				$ret[$key] = $this->__get($key);
				// Typeだったときの対処
				if(is_a($ret[$key], 'Type')) {
					$ret[$key]->output();
				}
			}
		}
		return $ret;
	}

	public function delete() {
		$this->deleted_at = Util::get_unix_time();
		$this->save();
	}

	public function restore() {
		$this->deleted_at = 0;
		$this->save();
	}

	public function set($data) {
		foreach($this->_data as $key => $value) {
			if(array_key_exists($key, $data)) {
				$this->_data[$key] = $value;
			}
		}
	}

	public function hibernate() {
		// ハイバネートの有効化チェック
		if(!$this->_enable_hibernate || !Config::get('hibernate', true))
		{
			return;
		}

		// モデルからテーブルカラム情報を取得
		$alter_flag = false;
		$model_name = $this->_name . 'Model';
		$define_columns = $model_name::getTableColumns();

		$ignore_columns = array('id', 'created_at', 'updated_at', 'deleted_at');

		foreach ($this->_data as $column => $value)
		{
			// 型が違うか定義されてないカラムをリストアップ
			if(!array_key_exists($column, $define_columns))
			{
				$new_type = self::get_mysql_type($value);
				MySQL::getInstance()->exec(
										sprintf('ALTER TABLE %s ADD %s %s',
												Util::tableize($this->_name),
												$column,
												$new_type)
										, array(), true);
				$define_columns[$column] = $new_type;
				$alter_flag = true;
			}
			else if(!in_array($column, $ignore_columns) && $define_columns[$column] !== self::get_mysql_type($value, $define_columns[$column]))
			{
				$new_type = self::get_mysql_type($value, $define_columns[$column]);
				MySQL::getInstance()->exec(
										sprintf('ALTER TABLE %s MODIFY COLUMN %s %s',
											Util::tableize($this->_name),
											$column,
											$new_type)
										, array(), true);			
				$define_columns[$column] = $new_type;
				$alter_flag = true;
			}
		}
		
		if ($alter_flag) {
			$model_name::setTableColumns($define_columns);
		}
	}

	private function validate() {
		$errors = array();
		foreach ($this->_data as $column => $value) {
			if(array_key_exists($column, $this->_properties) && array_key_exists('validation', $this->_properties[$column])) {
				$validation = $this->_properties[$column]['validation'];
				$validation->validate($value);
				$errors = array_merge($errors, $validation->getErrorMessages());
			}
		}


		if (count($errors) !== 0 && $this->_throw_exception) {
			throw new Exception(implode($errors, PHP_EOL));
		}

		if (count($errors) !== 0) {
			return false;
		}

		return true;
	}

	public function save($table = null) {
		$this->hibernate();

		if(!$this->validate()) {
			return;
		}

		// set array
		foreach ($this->_properties as $property_key => $property_value) {
			if(array_key_exists('type', $property_value) && $property_value['type'] === 'array') {
				$this->setArrayObject($this->id, $property_key, $this->_object_cache[$property_key]);
			}
		}

		$table = !is_null($table)?$table:Util::tableize($this->_name);
		$data = $this->_data;
		if(array_key_exists('id', $data) && $data['id'] !== 0) {
			//update
			$update_id = $data['id'];
			unset($data['id']);

			// updated_at
			$data['updated_at'] = Util::get_unix_time();

			$update_query = '';
			$update_values = array();
			foreach($data as $key => $value) {
				$update_query .= sprintf(' `%s` = ?,', $key);
				$update_values[] = $value;
			}
			$update_query = substr($update_query, 0, -1);

			$query = 'UPDATE `'.$table.'` SET '.$update_query.' WHERE `id`=?';
			$update_values[] = $update_id;
			$db = MySQL::getInstance();
			$db->exec($query, $update_values);
			return $this->_data['id'];
		}else{
			//insert
			$update_query = '';
			$update_values = array();
			// updated_at
			$data['created_at'] = Util::get_unix_time();
			$data['updated_at'] = Util::get_unix_time();
			foreach($data as $key => $value) {
				$update_query .= sprintf(' `%s` = ?,', $key);
				$update_values[] = $value;
			}
			$update_query = substr($update_query, 0, -1);

			$query = 'INSERT `'.$table.'` SET '.$update_query;
			$db = MySQL::getInstance();
			$id = $db->exec($query, $update_values, null, true);
			$this->_data['id'] = $id;
			return $id;
		}
	}


	/*
	 * 配列を格納するときのやつ
	 */
	protected function getArrayTableName($key) {
		return sprintf('_%s_%s', Util::convert_snake_case($this->getName()), $key);
	}

	protected function getArrayObject($id, $key) {
		$array_object = array();
		$result = MySQL::getInstance()->exec(sprintf('SELECT * FROM %s WHERE id = ?',
											$this->getArrayTableName($key)), array($id), true);
		foreach ($result as $row) {
			$array_object[$row['key']] = $row['value'];
		}
		$array_object = array_reverse($array_object);
		return $array_object;
	}

	protected function setArrayObject($id, $key, $array_object) {
		// UNSAFE! valie and key support only text object!
		MySQL::getInstance()->exec(sprintf('CREATE TABLE IF NOT EXISTS `%s` (
												`id` bigint(20) NOT NULL,
												`key` text NOT NULL,
												`value` text NOT NULL
												) ENGINE=MyISAM DEFAULT CHARSET=utf8',
											$this->getArrayTableName($key)), array(), true);
		MySQL::getInstance()->exec(sprintf('DELETE FROM `%s` WHERE `id` = ?',
											$this->getArrayTableName($key)), array($id), true);

		$query_strs = array();
		$query_values = array();
		foreach ($array_object as $array_key => $array_value) {
			$query_strs[] = '(?, ?, ?)';
			$query_values[] = $id;
			$query_values[] = $array_key;
			$query_values[] = $array_value;
		}
		MySQL::getInstance()->exec(sprintf('INSERT INTO `%s`(`id`, `key`, `value`) VALUES%s',
											$this->getArrayTableName($key), implode($query_strs, ',')), $query_values, true);
	}

	const MYSQL_TYPE_UNDEFINED  = 0;
	const MYSQL_TYPE_TINYINT    = 1;
	const MYSQL_TYPE_INT        = 2;
	const MYSQL_TYPE_TEXT       = 3;
	const MYSQL_TYPE_MEDIUMTEXT = 4;
	const MYSQL_TYPE_LONGTEXT   = 5;
	private static function get_mysql_type($value, $type_text = null) {
		$type = null;
		if (is_null($type_text)) {
			$type = self::MYSQL_TYPE_UNDEFINED;
		}
		if ($type_text == 'tinyint(1)') {
			$type = self::MYSQL_TYPE_TINYINT;
		}
		if ($type_text == 'int(11)') {
			$type = self::MYSQL_TYPE_INT;
		}
		if ($type_text == 'text') {
			$type = self::MYSQL_TYPE_TEXT;
		}
		if ($type_text == 'mediumtext') {
			$type = self::MYSQL_TYPE_MEDIUMTEXT;
		}
		if ($type_text == 'longtext') {
			$type = self::MYSQL_TYPE_LONGTEXT;
		}
		if (is_null($type)) {
			return $type_text;
		}

		if ($type <= self::MYSQL_TYPE_TINYINT && is_numeric($value) && strlen($value) == 1) {
			return 'tinyint(1)';
		}
		if ($type <= self::MYSQL_TYPE_INT && is_numeric($value)) {
			return 'int(11)';
		}
		if ($type <= self::MYSQL_TYPE_TEXT && strlen($value) < 65535) {
			return 'text';
		}
		if ($type <= self::MYSQL_TYPE_MEDIUMTEXT && strlen($value) < 16777215) {
			return 'mediumtext';
		}
		if ($type <= self::MYSQL_TYPE_LONGTEXT) {
			return 'longtext';
		}
	}
}
