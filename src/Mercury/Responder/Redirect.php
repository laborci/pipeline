<?php namespace Atomino2\Mercury\Responder;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;

class Redirect extends AbstractResponder {
	static public function setup($url = '/', $statusCode = 302) { return parent::setup(get_defined_vars()); }
	protected function respond(Response $response): Response|null { $this->redirect($this->arg('url'), $this->arg('statusCode')); }
}