<?php namespace Atomino2\Mercury\Router\Matcher;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PathMatcher extends Matcher {
	protected function match(Request &$request, string $pattern, ParameterBag|null $bag): bool {
		$matcher = new PatternProcessor('/', $pattern, PatternProcessor::TAILMODE_END);
		$result = $matcher->match(trim($request->getPathInfo(), '/'));
		if ($result) {
			$bag->add($matcher->getParameters());
			if ($tail = $matcher->getTail()) {
				$request = $request->duplicate(
					null,
					null,
					null,
					null,
					null,
					array_merge($request->server->all(), ['REQUEST_URI' => $tail])
				);
			}
		}
		return $result;
	}
}