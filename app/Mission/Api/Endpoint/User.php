<?php namespace App\Mission\Api\Endpoint;

use App\Carbonite\Store\UserStore;
use Atomino2\Mercury\EndpointResponder\Endpoint;
use Atomino2\Mercury\EndpointResponder\EndpointResponder;
use Atomino2\Mercury\EndpointResponder\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

class User extends EndpointResponder {

	public function __construct(private UserStore $userStore, EventDispatcher $dispatcher) {
	}

	#[Endpoint("/hello/:name=Elvis Presley", Endpoint::POST, Endpoint::GET)]
	protected function sayHello(): Response {
		$this->userStore->pick(1)->save(true);
		return new JsonResponse([
			"user"    => $this->userStore->pick(1)->name,
			"article" => $this->userStore->pick(1)->articles->first()->title,
			"message" => "Helloka " . $this->pathArgs->get("name"),
		]);
	}

	#[Endpoint("/goodbye/:name")]
	protected function sayGoodBye(): Response {
		return new Response("GoodBye " . $this->pathArgs->get("name"));
	}

}



