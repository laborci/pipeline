<?php namespace App\Router;

use App\Handler\WebHandler;
use Atomino2\Mercury\Router\Router;

class MySecondRouter extends Router {
	protected function route() {
		$this("/pipa")->pipe(WebHandler::setup("NAEZAMÁSIKCUCC"));
	}
}