<?php namespace App;

use Atomino2\Application\ApplicationInterface;

class ApplicationCli implements ApplicationInterface {
	public function run() {
		echo "my cli app is running";
	}
}