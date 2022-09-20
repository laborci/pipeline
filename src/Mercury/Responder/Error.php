<?php namespace Atomino2\Mercury\Responder;

use Atomino2\Mercury\Responder\AbstractResponder;
use Symfony\Component\HttpFoundation\Response;

class Error extends AbstractResponder {
	static public function setup(int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, string $message = "", string $type = "", string $code = "") { return parent::setup(get_defined_vars()); }
	protected function respond(Response $response): Response|null {
		$this->error(
			$this->arg("statusCode"),
			$this->arg("message"),
			$this->arg("type"),
			$this->arg("code")
		);
	}
}
