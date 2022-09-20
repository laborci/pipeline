<?php namespace App\Mission\Public\Page;

use Atomino2\Mercury\Responder\AbstractResponder;
use Symfony\Component\HttpFoundation\Response;

class IndexPage extends AbstractResponder {

	public static function setup(string $message): array { return parent::setup(get_defined_vars()); }

	public function respond(): Response {
		$response = new Response();
		$response->setContent($this->arg("message"));
		return $response;
	}
}