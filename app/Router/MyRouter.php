<?php namespace App\Router;

use App\Handler\WebHandler;
use Atomino2\Mercury\Router\Router;

class MyRouter extends Router {
	protected function route() {
		$this(path: "/valami/:var([0-9]{1,3})=1234/**")->pipe(WebHandler::setup("Helloka"));
		$this(path: "/valami/:var/**")->pipe(WebHandler::setup("Helloka"));
		$this(path: "/valami")->pipe(WebHandler::setup("Belloka"));
		$this(path: "/valami2/**")->pipe(MySecondRouter::class);
		$this()->pipe(WebHandler::setup("ERROR404"));
	}
}