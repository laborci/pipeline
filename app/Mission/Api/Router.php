<?php namespace App\Mission\Api;

use Atomino2\Mercury\Responder\Error;

class Router extends \Atomino2\Mercury\Router\Router {
	protected function route() {
		$this(path: "/user/**")->pipe(Endpoint\User::class);
		$this()->pipe(Error::setup(404,"", '', "ENDPOINT_NOT_FOUND"));
	}
}