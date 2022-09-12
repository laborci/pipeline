<?php

namespace Atomino2\Mercury\Router;

use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class Route extends Handler {

	public function __construct(
		private readonly Matcher\PathMatcher   $pathMatcher,
		private readonly Matcher\PortMatcher   $portMatcher,
		private readonly Matcher\SchemeMatcher $schemeMatcher,
		private readonly Matcher\MethodMatcher $methodMatcher,
		private readonly Matcher\HostMatcher   $hostMatcher,
		private readonly AttributeBag|null     $pathBag = null,
		private readonly AttributeBag|null     $hostBag = null,
	) {
	}

	public function handle(
		null|string|array|callable $method,
		null|string|array|callable $path,
		null|string|array|callable $host,
		null|string|array|callable $port,
		null|string|array|callable $scheme,
		Request                    $request
	) {
		$request = $request->duplicate();
		$pathBag = $this->pathBag ?: new ParameterBag();
		$hostBag = $this->hostBag ?: new ParameterBag();
		if (
			($this->pathMatcher)($request, $path, $pathBag) &&
			($this->hostMatcher)($request, $host, $hostBag) &&
			($this->portMatcher)($request, $port) &&
			($this->schemeMatcher)($request, $scheme) &&
			($this->methodMatcher)($request, $method)
		) {
			$this->setContext("hostBag", $hostBag);
			$this->setContext("pathBag", $pathBag);
			$this->setContext("request", $request);
			return $this->next();
		}
		$this->break();
	}
}

