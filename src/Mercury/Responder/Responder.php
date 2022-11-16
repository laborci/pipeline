<?php namespace Atomino2\Mercury\Responder;

use Atomino2\Mercury\AbstractRequestHandler;
use Atomino2\Mercury\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

abstract class Responder extends Handler {
	public function handle(): Response { return $this->respond(); }
	abstract protected function respond(): Response;
}