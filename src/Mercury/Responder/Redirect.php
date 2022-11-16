<?php namespace Atomino2\Mercury\Responder;

use Atomino2\Mercury\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class Redirect extends Handler {
	const ARG_URL         = 'url';
	const ARG_STATUS_CODE = 'status_code';
	public function handle(): Response|null { $this->redirect($this->args->get(self::ARG_URL), $this->args->get(self::ARG_STATUS_CODE)); }
}