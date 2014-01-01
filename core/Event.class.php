<?php

class Event {
	public static function addEventListener($event_name, $event_handler) {

	}
	public function __destruct() {
		echo View::getBuffer();
	}
}
$event = new Event();