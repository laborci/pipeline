<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Mercury\AbstractRequestHandler;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractMiddleware extends AbstractRequestHandler {
	public function run(): Response|null {
		parent::run();
		return $this->handle();
	}
	abstract protected function handle(): Response|null;
}