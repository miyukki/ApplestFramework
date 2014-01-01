<?php

/**
 * Type
 *
 * DBのカラムをデータとして持ち,値の正規判定もする
 * 
 * @packege MiyukkiFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 * @version $id$
 */
class Type {
	private $_name;
	private $_data;
	private $_format;
	private $_throw_exception = true;

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
		// $this->_properties = $this->_default_properties+$this->_properties;
		// var_dump($this->_properties);
		// タイプフォーマットの取得
		// $formats = $this->getFormat();
		// $this->_format = array();
		// タイプフォーマットからデータ値の初期設定とフォーマット保存
		// 20131004 初期値なしへ
		// foreach($formats as $key => $format) {
			// $this->_data[$key] = $format[0];
			// $this->_format[$key] = $format[1];
		// }
		$this->_data = array();
		$this->_format = $this->getFormat();
		// var_dump($this->getFormat());
		// データが有る場合はデータを代入
		if(!is_null($data)) {
			/*
			foreach($data as $data_key => $data_value) {
				$this->$data_key = $data_value;
			}
			*/
			$this->_data = $data;
		}
	}
	/**
	 * __setオーバーロード
	 * データにキーと値を代入,その際に正規性を確認
	 */
	public function __set($key, $value) {
				$this->_data[$key] = $value;
				return;
		if(array_key_exists($key, $this->_format) && in_array('ARRAY', $this->_format[$key])) {
			$this->_data[$key] = json_encode($value);
		}else{
			$check_result = true;
			if(array_key_exists($key, $this->_format)) {
				foreach($this->_format[$key] as $filter) {
					$check_result = $this->_check($value, $filter);
					if($check_result === false) break;
				}
			}
			if($check_result) {
			}else{
				if($this->_throw_exception) {
					throw new Exception();
				}
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
		if(array_key_exists($key.'_id', $this->_data)) {
			$model_name = Util::untableize($key).'Model';
			return $model_name::find_by_id($this->_data[$key.'_id']);
		}
		
		if(!array_key_exists($key, $this->_data)) {
			return NULL;
		}
		// if(array_key_exists($key, $this->_format) && in_array('ARRAY', $this->_format[$key])) {
		// 	return json_decode($this->_data[$key], true);
		// }
		if(is_array($this->_properties) && array_key_exists($key, $this->_properties) && array_key_exists('type', $this->_properties[$key])) {
			switch ($this->_properties[$key]['type']) {
				case 'int':
					$this->_data[$key] = (int)$this->_data[$key];
					break;
			}
		}
		return $this->_data[$key];
	}
	
	/**
	 * 正規性を確認
	 * 
	 * @param mixed $value チェックする値
	 * @param string $filter チェックするフィルタ,/^.+$/とすると正規表現フィルタが利用可能
	 */
	private function _check($value, $filter) {
		if(preg_match('/^\/(.+)\/[A-z]+$/', $filter, $maches)) {
			return preg_match($maches[1], $value) == 1;
		}
		if($filter == 'NUMRIC') {
			return preg_match('/^[0-9]+$/', $value) == 1;
		}
		if($filter == 'MAIL') {
			return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
		}
		if(strpos($filter, 'LENGTH_MIN:') === 0) {
			$length = substr($filter, strlen('LENGTH_MIN:'));
			return mb_strlen($value) >= $length;
		}
		if(strpos($filter, 'LENGTH_MAX:') === 0) {
			$length = substr($filter, strlen('LENGTH_MAX:'));
			return mb_strlen($value) <= $length;
		}
		return true;
	}
	protected function getFormat() {
		return array();
	}
	public function setName($name) {
		$this->_name = $name;
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

	public function save($table = null) {
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
}