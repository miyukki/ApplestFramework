<?php

class Event {
	public function __construct() {

	}

	public static function addEventListener($event_name, $event_handler) {

	}
	
	public function __destruct() {
		echo View::getBuffer();
		Log::finalize();
	}
}
$event = new Event();