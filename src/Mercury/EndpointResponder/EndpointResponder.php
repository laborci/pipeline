<?php namespace Atomino2\Mercury\EndpointResponder;

use Atomino2\Mercury\Pipeline\Handler;
use Atomino2\Mercury\Pipeline\PatternMatcher;
use Symfony\Component\HttpFoundation\Response;

class EndpointResponder extends Handler {

	public function handle(): Response|null {
		foreach ((new \ReflectionClass($this))->getMethods() as $method) {
			$endpoints = $method->getAttributes(Endpoint::class);
			if (count($endpoints)) {
				/** @var Endpoint $endpoint */
				$endpoint = $endpoints[0]->newInstance();
				if (
					(is_null($endpoint->methods) || in_array($this->request->getMethod(), $endpoint->methods)) &&
					$this->pathMatch($endpoint->route)
				) {
					return $method->invoke($this);
				}
			}
		}
		return null;
	}

	private function pathMatch(string $pattern): bool {
		$matcher = new PatternMatcher('/', $pattern, PatternMatcher::TAIL_MODE_END);
		$result = $matcher->match(trim($this->request->getPathInfo(), '/'));
		if ($result) $this->pathArgs->add($matcher->getParameters());
		return $result;
	}
}