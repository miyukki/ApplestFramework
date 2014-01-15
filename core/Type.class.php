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

	protected $_output_keys = array();
	
	public function __construct($data = null) {
		$this->_properties = array_merge($this->_default_properties, $this->_properties);

		// データが有る場合はデータを代入
		if(!is_null($data))
		{
			$this->_data = $data;
		}
	}

	public function setName($name) {
		$this->_name = $name;
	}

	/**
	 * __setオーバーロード
	 * データにキーと値を代入,その際に正規性を確認
	 */
	public function __set($key, $value) {
		$this->_data[$key] = $value;
	}

	/**
	 * __callオーバーロード
	 * 
	 */
	public function __call($name, $arguments) {
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
	 * __getオーバーロード
	 * データからキーを取得
	 * 
	 * @param string $key 取得するキー
	 */
	public function __get($key) {
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
				$define_columns[$colum] = $new_type;
				$alter_flag = true;
			}
			else if(!in_array($column, $ignore_columns) && $define_columns[$key] !== self::get_mysql_type($value, $define_columns[$key]))
			{
				$new_type = self::get_mysql_type($value, $define_columns[$key]);
				MySQL::getInstance()->exec(
										sprintf('ALTER TABLE %s MODIFY COLUMN %s %s',
											Util::tableize($this->_name),
											$colum,
											$new_type)
										, array(), true);			
				$define_columns[$colum] = $new_type;
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
			if(array_key_exists($key, $this->_properties) && array_key_exists('validation', $this->_properties[$key])) {
				$validation = $this->_properties[$key]['validation'];
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
		$this->validate();

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
