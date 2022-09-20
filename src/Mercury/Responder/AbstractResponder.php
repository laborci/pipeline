<?php namespace Atomino2\Mercury\Responder;

use Atomino2\Mercury\AbstractRequestHandler;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractResponder extends AbstractRequestHandler {
	public function run(): Response|null {
		parent::run();
		return $this->respond();
	}
	abstract protected function respond(): Response|null;
}