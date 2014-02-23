<?php

class Validation {
	public static function create($name = '') {
		return new self($name);
	}

	/**
	 * @var バリデーションの名前
	 */
	private $name;

	/**
	 * @var バリデーションのルールたち
	 */
	private $rules = array();

	private $results = array();

	private $errors = array();

	private function __construct($name = '') {
		$this->name = $name;
	}

	public function rule($rule) {
		$args = array_slice(func_get_args(), 1);
		$this->rules[$rule] = $args;
		return $this;
	}

	public function getErrorMessages() {
		return $this->errors;
	}

	public function validate($subject) {
		$is_valid = true;

		foreach ($this->rules as $rule => $args) {
			switch ($rule) {
				case 'required':
					$this->results[$rule] = $flag =  Validator::required($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは必須です", $this->name);
					break;

				case 'min_length':
					$this->results[$rule] = $flag =  Validator::min_length($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字以上にしてください", $this->name, $args[0]);
					break;

				case 'max_length':
					$this->results[$rule] = $flag =  Validator::max_length($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字以下にしてください", $this->name, $args[0]);
					break;

				case 'exact_length':
					$this->results[$rule] = $flag =  Validator::exact_length($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字にしてください", $this->name, $args[0]);
					break;

				case 'mb_min_length':
					$this->results[$rule] = $flag =  Validator::mb_min_length($subject, $args[0], @$args[1]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字以上にしてください", $this->name, $args[0]);
					break;

				case 'mb_max_length':
					$this->results[$rule] = $flag =  Validator::mb_max_length($subject, $args[0], @$args[1]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字以下にしてください", $this->name, $args[0]);
					break;

				case 'mb_exact_length':
					$this->results[$rule] = $flag =  Validator::exact_length($subject, $args[0], @$args[1]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは%s文字にしてください", $this->name, $args[0]);
					break;

				case 'match_value':
					$this->results[$rule] = $flag =  Validator::match_value($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正確ではありません", $this->name);
					break;

				case 'match_pattern':
					$this->results[$rule] = $flag =  Validator::match_pattern($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正確ではありません", $this->name);
 					break;

				case 'valid_date':
					$this->results[$rule] = $flag =  Validator::valid_date($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは日付である必要があります", $this->name);
					break;

				case 'valid_email':
					$this->results[$rule] = $flag =  Validator::valid_email($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいメールアドレス形式ではありません", $this->name);
					break;

				case 'valid_strict_email':
					$this->results[$rule] = $flag =  Validator::valid_strict_email($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいメールアドレス形式ではありません", $this->name);
					break;

				case 'valid_url':
					$this->results[$rule] = $flag =  Validator::valid_url($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいURL形式ではありません", $this->name);
					break;

				case 'valid_uri':
					$this->results[$rule] = $flag =  Validator::valid_uri($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいURI形式ではありません", $this->name);
					break;

				case 'valid_ipv4':
					$this->results[$rule] = $flag =  Validator::valid_ipv4($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいIPv4アドレスではありません", $this->name);
					break;

				case 'valid_ipv6':
					$this->results[$rule] = $flag =  Validator::valid_ipv6($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは正しいIPv6アドレスではありません", $this->name);
					break;

				case 'numeric':
					$this->results[$rule] = $flag =  Validator::numeric($subject);
					if (!$flag) $this->errors[$rule] = sprintf("%sは数値である必要があります", $this->name);
					break;

				case 'min_number':
					$this->results[$rule] = $flag =  Validator::min_number($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは最小値を超えています", $this->name);
					break;

				case 'max_number':
					$this->results[$rule] = $flag =  Validator::max_number($subject, $args[0]);
					if (!$flag) $this->errors[$rule] = sprintf("%sは最大値を超えています", $this->name);
					break;
			}

			if($is_valid && $this->results[$rule] === false) {
				$is_valid = false;
				$this->results[$rule] = array_pop($args);
			}
		}

		return $is_valid;
	}
}