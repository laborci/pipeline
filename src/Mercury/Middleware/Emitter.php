<?php namespace Atomino2\Mercury\Middleware;

use Symfony\Component\HttpFoundation\Response;

class Emitter extends AbstractMiddleware {
	protected function handle(): Response|null {
		$response = $this->next();
		if (is_null($response)) $response = new Response(null, 404);
		return $response->send();
	}
}	