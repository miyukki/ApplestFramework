<?php

class Error extends Exception {
	private $status;

	public function __construct($message, $status = 500, $code = 0, Exception $previous = null) {
		$this->status = $status;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode() {
    	return $this->status;
    }
}