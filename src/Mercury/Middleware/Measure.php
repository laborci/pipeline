<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Mercury\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class Measure extends Handler {
	public function handle(): Response|null {
		$response = $this->next();
		$runtime = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000,2);
		$response->headers->set('x-runtime', $runtime);
		debug("Runtime: ". $runtime.'ms');
		return $response;
	}
}