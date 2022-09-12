<?php namespace Atomino2\Mercury\Router\Matcher;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

abstract class Matcher {
	public function __invoke(Request &$request, null|string|array|callable $matchWith, ParameterBag|null $bag = null): bool {
		if (is_null($matchWith)) return true;
		if (is_callable($matchWith)) return $matchWith($request);
		if (is_string($matchWith)) $matchWith = [$matchWith];
		foreach ($matchWith as $pattern) if ($this->match($request, $pattern, $bag)) return true;
		return false;
	}

	abstract protected function match(Request &$request, string $pattern, ParameterBag|null $bag): bool;
}
