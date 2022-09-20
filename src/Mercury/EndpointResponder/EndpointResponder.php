<?php namespace Atomino2\Mercury\EndpointResponder;

use Atomino2\Mercury\Responder\AbstractResponder;
use Atomino2\Mercury\Router\Matcher\MethodMatcher;
use Atomino2\Mercury\Router\Matcher\PathMatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class EndpointResponder extends AbstractResponder {

	protected function respond(Response $response): Response|null {
		$methodMatcher = new MethodMatcher();
		$pathMatcher = new PathMatcher();

		foreach ((new \ReflectionClass($this))->getMethods() as $method) {
			$endpoints = $method->getAttributes(Endpoint::class);
			if (count($endpoints)) {
				/** @var Endpoint $endpoint */
				$endpoint = $endpoints[0]->newInstance();
				if (
					$methodMatcher($this->request, $endpoint->methods) &&
					$pathMatcher($this->request, $endpoint->route, $args = new ParameterBag())
				) return $method->invoke($this, $args);
			}
		}
		$this->break();
	}
}