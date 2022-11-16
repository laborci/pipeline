<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Mercury\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class Emitter extends Handler {
	public function handle(): Response|null { return ($this->next() ?? new Response(null, 404))->send(); }
}	