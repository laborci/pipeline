<?php namespace Atomino2\Mercury\Middleware;

use Symfony\Component\HttpFoundation\Response;

class Measure extends AbstractMiddleware {
	public function handle(): Response|null {
		$response = $this->next();
		$response->headers->set('x-runtime', microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);
		return $response;
	}
}