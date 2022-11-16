<?php namespace Atomino2\Mercury\Responder;

use Symfony\Component\HttpFoundation\Response;

class Error extends Responder {
	const ARG_STATUS_CODE = 'status_code';
	const ARG_MESSAGE     = 'message';
	const ARG_TYPE        = 'type';
	const ARG_CODE        = 'code';

	protected function respond(): Response {
		$this->error(
			$this->args->get("statusCode", 404),
			$this->args->get("message", ""),
			$this->args->get("type", ""),
			$this->args->get("code", -1)
		);
	}
}
