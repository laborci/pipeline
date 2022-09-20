<?php namespace App\Mission\Api\Endpoint;

use Atomino2\Mercury\EndpointResponder\Endpoint;
use Atomino2\Mercury\EndpointResponder\EndpointResponder;
use Atomino2\Mercury\EndpointResponder\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class User extends EndpointResponder {

	#[Endpoint("/hello/:name=Elvis Presley", Endpoint::POST, Endpoint::GET)]
	protected function sayHello(): Response {
		return new JsonResponse(["message" => "Hello ".$this->getPathArg("name")]);
	}

	#[Endpoint("/goodbye/:name")]
	protected function sayGoodBye(): Response {
		return new Response("GoodBye " .$this->getPathArg("name"));
	}

}



