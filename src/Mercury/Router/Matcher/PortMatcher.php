<?php namespace Atomino2\Mercury\Router\Matcher;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PortMatcher extends Matcher {
	protected function match(Request &$request, string $pattern, ParameterBag|null $bag): bool { return $request->getPort() === $pattern; }
}