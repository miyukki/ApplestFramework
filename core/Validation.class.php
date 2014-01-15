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

	private function __construct($name = null) {
		$this->name = $name;
	}

	public function rule($rule) {
		$args = array_slice(func_get_args(), 1);
		$this->rules[$rule] = $args;
		return $this;
	}

	public function getErrorMessages() {
		$errors = array();
		foreach ($this->results as $rule => $result) {
			if($result !== true) {
				array_push($errors, $result);
			}
		}
		return $errors;
	}

	public function validate($subject) {
		$is_valid = true;

		foreach ($this->rules as $rule => $args) {
			switch ($rule) {
				case 'required':
					$this->results[$rule] = Validator::required($subject);
					break;

				case 'min_length':
					$this->results[$rule] = Validator::min_length($subject, $args[0]);
					break;

				case 'max_length':
					$this->results[$rule] = Validator::max_length($subject, $args[0]);
					break;

				case 'exact_length':
					$this->results[$rule] = Validator::exact_length($subject, $args[0]);
					break;

				case 'mb_min_length':
					$this->results[$rule] = Validator::mb_min_length($subject, $args[0], @$args[1]);
					break;

				case 'mb_max_length':
					$this->results[$rule] = Validator::mb_max_length($subject, $args[0], @$args[1]);
					break;

				case 'mb_exact_length':
					$this->results[$rule] = Validator::exact_length($subject, $args[0], @$args[1]);
					break;

				case 'match_value':
					$this->results[$rule] = Validator::match_value($subject, $args[0]);
					break;

				case 'match_pattern':
					$this->results[$rule] = Validator::match_pattern($subject, $args[0]);
					break;

				case 'valid_date':
					$this->results[$rule] = Validator::valid_date($subject);
					break;

				case 'valid_email':
					$this->results[$rule] = Validator::valid_email($subject);
					break;

				case 'valid_strict_email':
					$this->results[$rule] = Validator::valid_strict_email($subject);
					break;

				case 'valid_url':
					$this->results[$rule] = Validator::valid_url($subject);
					break;

				case 'valid_uri':
					$this->results[$rule] = Validator::valid_uri($subject);
					break;

				case 'valid_ipv4':
					$this->results[$rule] = Validator::valid_ipv4($subject);
					break;

				case 'valid_ipv6':
					$this->results[$rule] = Validator::valid_ipv6($subject);
					break;

				case 'numeric':
					$this->results[$rule] = Validator::numeric($subject);
					break;

				case 'min_number':
					$this->results[$rule] = Validator::min_number($subject, $args[0]);
					break;

				case 'max_number':
					$this->results[$rule] = Validator::max_number($subject, $args[0]);
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