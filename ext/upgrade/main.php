<?php

class Upgrade extends Extension {
	public function receive_event($event) {
		if(is_a($event, 'InitExtEvent')) {
			$this->do_things();
		}
	}

	private function do_things() {
		global $config;
		global $database;

		if(!is_numeric($config->get_string("db_version"))) {
			$config->set_int("db_version", 2);
		}
		
		if($config->get_int("db_version") < 6) {
			$database->upgrade_schema("ext/upgrade/schema.xml");
		}
	}
}
add_event_listener(new Upgrade(), 5);
?>