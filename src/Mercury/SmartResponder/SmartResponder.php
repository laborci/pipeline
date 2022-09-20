<?php namespace Atomino2\Mercury\SmartResponder;

use Atomino2\Mercury\Responder\AbstractResponder;
use Symfony\Component\HttpFoundation\Response;

class SmartResponder extends AbstractResponder {

	protected function respond(): Response|null {

		return new Response("Helo");
	}
}