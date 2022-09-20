<?php namespace App\Mission\Api\Endpoint;

use Atomino2\Mercury\EndpointResponder\Endpoint;
use Atomino2\Mercury\EndpointResponder\EndpointResponder;
use Atomino2\Mercury\EndpointResponder\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class User extends EndpointResponder {

	#[Endpoint("/hello/:name=Elvis Presley", Endpoint::POST, Endpoint::GET)]
	protected function sayHello(ParameterBag $args): Response {
		return new JsonResponse(["message" => "Hello " . $args->get("name")]);
	}

	#[Endpoint("/goodbye/:name")]
	protected function sayGoodBye(ParameterBag $args): Response {
		return new Response("GoodBye " . $args->get("name"));
	}
}



