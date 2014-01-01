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
	static public function getName() {
		return substr(static::$_name, 0, -5);
	}
	static public function create() {
		$type_name = self::getName().'Type';
		$type = new $type_name();
		$type->setName(self::getName());
		$id = $type->save();
		$type = static::find_by_id($id);
		return $type;
	}
	static public function __callStatic($name, $arguments) {
		if(strpos($name, 'find_by_') === 0) {
			$colum = substr($name, strlen('find_by_'));
			return self::find("*", array('condition' => array($colum => $arguments[0]), 'only' => true));
		}
		if(strpos($name, 'find_or_create_by_') === 0) {
			$colum = substr($name, strlen('find_or_create_by_'));
			$row = self::find("*", array('condition' => array($colum => $arguments[0]), 'only' => true));
			if(is_null($row)) {
				$row = self::create();
				$row->$colum = $arguments[0];
			}
			return $row;
		}
	}
	static public function find($columns = '*', $options = array()) {
		if(isset($options['only'])) $options['limit'] = 1;
		$t_name = isset($options['from'])?$options['from']:Util::tableize(self::getName());
		$query = 'SELECT '.$columns.' FROM '.$t_name.' ';
		$query_values = array();

		/* left join */
		if(isset($options['left_join'])) {
			$query .= ' LEFT JOIN '.Util::tableize($options['left_join'][0]).' ON '.Util::tableize($options['left_join'][0]).'.'.$options['left_join'][1].'='.Util::tableize(self::getName()).'.'.$options['left_join'][2];
			// problem
			if(isset($options['left_where'])) {
				$query .= ' AND '.Util::tableize($options['left_join'][0]).'.'.$options['left_where'];
			}
		}

		/* condition */
		if(isset($options['condition'])) {
			$query .= ' WHERE ';
			if(!is_array($options['condition'])) {
				$query .= $options['condition'];
				if(!isset($options['rescure'])) {
					$query .= ' AND '.Util::tableize(self::getName()).'.deleted_at = 0';
				}
			}else if(Util::is_hash($options['condition'])) {
				foreach($options['condition'] as $key => $value) {
					$query .= sprintf('`%s`.`%s` = ? AND', Util::tableize(self::getName()), $key);
					$query_values[] = $value;
				}
				if(!isset($options['rescure'])) {
					$query .= ' '.Util::tableize(self::getName()).'.deleted_at = 0';
				} else {
					$query = substr($query, 0, -3);
				}
			}else{
				// ここでテーブル名を付与する必要あり
				$where = $options['condition'];
				$where_query = array_shift($where);
				
				if(!isset($options['rescure'])) {
					$where_query .= ' AND '.Util::tableize(self::getName()).'.deleted_at = 0';
				}
				$query .= $where_query;
				foreach($where as $value) {
					$query_values[] = $value;
				}
			}
		}else{
			if(!isset($options['rescure'])) {
				$query .= ' WHERE '.Util::tableize(self::getName()).'.deleted_at = 0';
			}
		}

		/* group */
		if(isset($options['group'])) $query .= " GROUP BY ".$options['group'];

		/* order */
		if(isset($options['order'])) $query .= " ORDER BY ".$options['order'];

		/* limit & offset */
		if(isset($options['limit'])) $query .= " LIMIT ".$options['limit'];
		if(isset($options['offset'])) $query .= " OFFSET ".$options['offset'];
		$db = MySQL::getInstance();

		$ret = array();
		foreach ($db->exec($query, $query_values, true) as $data) {
			$type = self::getName().'Type';
			$type = new $type($data);
			$type->setName(self::getName());
			$ret[] = $type;
		}
		if(isset($options['only'])) {
			if(!isset($ret[0])) {
				return null;
			}
			if(isset($options['array'])) {
				return $ret[0]->getData();
			}
			// if(isset($options['public_array'])) {
			// 	return $ret[0]->getPublicData();
			// }
			return $ret[0];
		}
		if(isset($options['array'])) {
			$ret2 = array();
			foreach ($ret as $type) {
				$ret2[] = $type->getData();
			}
			return $ret2;
		}
		// if(isset($options['public_array'])) {
		// 	$ret2 = array();
		// 	foreach ($ret as $type) {
		// 		$ret2[] = $type->getPublicData();
		// 	}
		// 	return $ret2;
		// }
		return $ret;
	}
}