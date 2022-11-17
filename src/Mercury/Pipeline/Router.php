<?php namespace Atomino2\Mercury\Pipeline;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Router extends Handler {

	private bool     $routed          = false;
	private ?Request $modifiedRequest = null;

	public function handle(): ?Response {
		$this->route();
		return $this->next($this->modifiedRequest);
	}

	abstract protected function route(): void;

	public function __invoke(
		string|null|array $path = null,
		string|null       $method = null,
		string|null       $host = null,
		string|null       $port = null,
		string|null       $scheme = null
	): ?static {
		if ($this->routed) return null;
		$this->routed = (
			(is_null($port) || $this->request->getPort() === $port) &&
			(is_null($method) || $this->request->getMethod() === $method) &&
			(is_null($scheme) || $this->request->getScheme() === $scheme) &&
			(is_null($host) || $this->hostMatch($host)) &&
			(is_null($path) || $this->pathMatch($path))
		);
		return $this->routed ? $this : null;
	}

	private function hostMatch(string $pattern): bool {
		$matcher = new PatternMatcher('.', $pattern, PatternMatcher::TAIL_MODE_NONE);
		$result = $matcher->match($this->request->getHost());
		if ($result) $this->hostArgs->add($matcher->getParameters());
		return $result;
	}

	private function pathMatch(string $pattern): bool {
		$matcher = new PatternMatcher('/', $pattern, PatternMatcher::TAIL_MODE_END);
		$result = $matcher->match(trim($this->request->getPathInfo(), '/'));
		if ($result) {
			$this->pathArgs->add($matcher->getParameters());
			if (($tail = $matcher->getTail()) !== false) {
				$this->modifiedRequest = $this->request->duplicate(
					null, null, null, null, null,
					array_merge($this->request->server->all(), ['REQUEST_URI' => $tail])
				);
			}
		}
		return $result;
	}
}