<?php namespace App\Mission\Api;

use Atomino2\Mercury\Responder\Error;

class Router extends \Atomino2\Mercury\Pipeline\Router {
	protected function route():void {
		$this(path: "/user/**")?->pipe(Endpoint\User::class);
		$this()?->pipe(Error::class,[Error::ARG_MESSAGE=>"picsa"]);
	}
}