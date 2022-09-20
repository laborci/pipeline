<?php

namespace Atomino2\Mercury\Router;

use Atomino2\Mercury\AbstractRequestHandler;
use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class Route extends AbstractRequestHandler {

	public static function setup(
		string|null|array $path = null,
		string|null       $method = null,
		string|null       $host = null,
		string|null       $port = null,
		string|null       $scheme = null
	) {
		return parent::setup(get_defined_vars());
	}

	public function __construct(
		private readonly Matcher\PathMatcher   $pathMatcher,
		private readonly Matcher\PortMatcher   $portMatcher,
		private readonly Matcher\SchemeMatcher $schemeMatcher,
		private readonly Matcher\MethodMatcher $methodMatcher,
		private readonly Matcher\HostMatcher   $hostMatcher,
	) {
	}

	public function run() {
		parent::run();
		$method = $this->arg("method");
		$path = $this->arg("path");
		$host = $this->arg("host");
		$port = $this->arg("port");
		$scheme = $this->arg("scheme");

		$request =  $this->request->duplicate();

		$pathArgs = $this->getContext("path-args");
		$hostArgs = $this->getContext("host-args");

		if (
			($this->pathMatcher)($request, $path, $pathArgs) &&
			($this->hostMatcher)($request, $host, $hostArgs) &&
			($this->portMatcher)($request, $port) &&
			($this->schemeMatcher)($request, $scheme) &&
			($this->methodMatcher)($request, $method)
		) {
			$this->setContext("request", $request);
			return $this->next();
		}
		$this->break();
	}
}

