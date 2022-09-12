<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class Measure extends Handler {
	public function handle(): Response {
		$response = $this->next();
		$response->headers->set('x-runtime', microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);
		return $response;
	}
}