<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class Emitter extends Handler {
	public function handle(): Response|null {
		$response = $this->next();
		if (is_null($response))  $response = new Response(null, 404);
		$response->send();
		return $response;
	}
}	