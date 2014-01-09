<?php

/**
 * Model
 *
 * ActiveRecord のように扱うことが出きるモデルです
 * 
 * @package ApplestFramework
 * @author miyukki<toriimiyukki@gmail.com>
 * @since PHP 5.3
 * @version 1.1
 */

// オプション    説明
// :condition   検索条件を指定
// :offset  オフセット値
// :limit   取得件数
// :order   ソート
// :select  取得したデータのカラム
// :group   グルーピング命令
// :joins   テーブル結合
// :include 関連テーブルのデータを取得
// :readonly    trueが設定されていると、保存できない
// :from    selectに挿入されるテーブル名をオーバーライトできる
// :lock    データベースにロック

class Model {
	protected static $instance = null;

    protected static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    private $table_columns = array();
    private function __construct() {
    	MySQL::getInstance()->exec(sprintf('CREATE TABLE IF NOT EXISTS `%s` (
												`id` int(11) NOT NULL AUTO_INCREMENT,
												`created_at` int(11) NOT NULL,
												`updated_at` int(11) NOT NULL,
												`deleted_at` int(11) NOT NULL,
												PRIMARY KEY (`id`)
												) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8',
											$this->getTableizeName()), array(), true);

    	// カラムデータを取得
    	$columns = MySQL::getInstance()->exec('SHOW COLUMNS FROM ' . $this->getTableizeName(), array(), true);

    	// $this->table_columnsに代入
    	foreach ($columns as $column) {
    		$this->table_columns[$column['Field']] = $column['Type'];
    	}
    }

    public static function getTableColumns() {
    	return static::getInstance()->table_columns;
    }

    public static function setTableColumns($columns) {
    	return static::getInstance()->table_columns = $columns;
    }

    private function getModelName() {
		$called_class = get_called_class();
		return substr($called_class, 0, -strlen('Model'));
    }

    private function getTableizeName() {
		return Util::tableize($this->getModelName());
    }


	public static function create() {
		return static::getInstance()->_create();
	}

	private function _create() {
		$type_name = $this->getTableizeName().'Type';
		$type = new $type_name();
		$type->setName($this->getModelName());
		$id = $type->save();
		// $type = static::find_by_id($id);
		return $type;
	}


	static public function __callStatic($name, $arguments) {
		if(strpos($name, 'find_by_') === 0) {
			$colum = substr($name, strlen('find_by_'));
			return static::find("*", array('condition' => array($colum => $arguments[0]), 'only' => true));
		}
		if(strpos($name, 'find_or_create_by_') === 0) {
			$colum = substr($name, strlen('find_or_create_by_'));
			$row = static::find("*", array('condition' => array($colum => $arguments[0]), 'only' => true));
			if(is_null($row)) {
				$row = static::create();
				// $row->$colum = $arguments[0];
			}
			return $row;
		}
	}

	public static function find($columns = '*', $options = array()) {
		return static::getInstance()->_find($columns , $options);
	}

	private function _find($columns, $options) {
		// only属性が付いている場合
		if(isset($options['only']))
		{
			$options['limit'] = 1;	
		}

		$table_name = $this->getTableizeName();
		if(isset($options['from']))
		{
			$table_name = $options['from'];
		}

		$query = sprintf('SELECT %s FROM %s', $columns, $table_name);
		$query_values = array();

		/* left join */
		if(isset($options['left_join']))
		{
			$query .= sprintf(' LEFT JOIN %s ON %s.%s = %s.%s ',
									Util::tableize($options['left_join'][0]),
									Util::tableize($options['left_join'][0]), $options['left_join'][1],
									$this->getTableizeName(), $options['left_join'][2]);
			// problem
			if(isset($options['left_where']))
			{
				$query .= ' AND '.Util::tableize($options['left_join'][0]).'.'.$options['left_where'];
			}
		}

		/* condition */
		if(isset($options['condition']))
		{
			$query .= ' WHERE ';
			if(!is_array($options['condition'])) {
				$query .= $options['condition'];
				if(!isset($options['rescure'])) {
					$query .= ' AND '.$this->getTableizeName().'.deleted_at = 0';
				}
			}else if(Util::is_hash($options['condition'])) {
				foreach($options['condition'] as $key => $value) {
					$query .= sprintf('`%s`.`%s` = ? AND', $this->getTableizeName(), $key);
					$query_values[] = $value;
				}
				if(!isset($options['rescure'])) {
					$query .= ' '.$this->getTableizeName().'.deleted_at = 0';
				} else {
					$query = substr($query, 0, -3);
				}
			}else{
				// ここでテーブル名を付与する必要あり
				$where = $options['condition'];
				$where_query = array_shift($where);
				
				if(!isset($options['rescure'])) {
					$where_query .= ' AND '.$this->getTableizeName().'.deleted_at = 0';
				}
				$query .= $where_query;
				foreach($where as $value) {
					$query_values[] = $value;
				}
			}
		}else{
			if(!isset($options['rescure'])) {
				$query .= ' WHERE '.$this->getTableizeName().'.deleted_at = 0';
			}
		}

		/* group */
		if(isset($options['group']))
		{
			$query .= " GROUP BY ".$options['group'];
		}

		/* order */
		if(isset($options['order']))
		{
			$query .= " ORDER BY ".$options['order'];
		}

		/* limit & offset */
		if(isset($options['limit']))
		{
			$query .= " LIMIT ".$options['limit'];
		}

		if(isset($options['offset']))
		{
			$query .= " OFFSET ".$options['offset'];
		}

		$db = MySQL::getInstance();

		$ret = array();
		foreach ($db->exec($query, $query_values, true) as $data) {
			$type_name = $this->getTableizeName().'Type';
			$type = new $type_name($data);
			$type->setName($this->getModelName());
			$ret[] = $type;
		}
		if(isset($options['only'])) {
			if(!isset($ret[0])) {
				return null;
			}
			if(isset($options['array'])) {
				return $ret[0]->getData();
			}
			return $ret[0];
		}
		if(isset($options['array'])) {
			$ret2 = array();
			foreach ($ret as $type) {
				$ret2[] = $type->getData();
			}
			return $ret2;
		}

		return $ret;
	}
}