<?php namespace Atomino2\Mercury\Router\Matcher;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class HostMatcher extends Matcher {
	protected function match(Request &$request, string $pattern, ParameterBag|null $bag): bool {
		$matcher = new PatternProcessor('.', $pattern, PatternProcessor::TAILMODE_NONE);
		$result = $matcher->match(trim($request->getPathInfo(), '/'));
		if ($result) {
			$bag->add($matcher->getParameters());
		}
		return $result;
	}
}
