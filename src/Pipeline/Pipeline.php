<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\EndOfPipelineException;
use DI\Container;

class Pipeline {
	public function __construct(private readonly Container $di, private array $context, private array $segments) { }

	/**
	 * @throws \DI\NotFoundException
	 * @throws EndOfPipelineException
	 * @throws \ReflectionException
	 * @throws \DI\DependencyException
	 */
	public function next() {
		if (count($this->segments) === 0) throw new EndOfPipelineException();
		$segment = array_shift($this->segments);
		[$handler, $arguments] = $segment;
		$handler = $this->makeHandler($handler, $arguments, count($this->segments) === 0);
		return $this->runHandler($handler, $arguments, $this->context);
	}

	public function break() { throw new Exceptions\BreakException(); }
	public function getContext(string|null $key = null) { return is_null($key) ? $this->context : (array_key_exists($key, $this->context) ? $this->context[$key] : null); }
	public function setContext(string $key, mixed $value) { $this->context[$key] = $value; }

	/**
	 * @throws \DI\NotFoundException
	 * @throws \ReflectionException
	 * @throws \DI\DependencyException
	 */
	private function makeHandler(string|Handler $handler, array $arguments, bool $lastHandler): Handler {
		if (is_string($handler)) {
			$handler = $this->di->make($handler);
			return $this->makeHandler($handler, $arguments, $lastHandler);
		}

		/* Inject pipeline */
		\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("pipeline", $this);

		/* Inject isLastHandler*/
		if ($lastHandler) \Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("isLastHandler", $lastHandler);

		return $handler;
	}

	/**
	 * @throws \ReflectionException
	 */
	private function runHandler(Handler $handler, array $arguments, array $context) {
		$handlerFunc = (new \ReflectionMethod($handler, "handle"));
		$pass = $arguments;
		$parameters = $handlerFunc->getParameters();
		for ($i = count($arguments); $i < count($parameters); $i++) {
			$param = $parameters[$i];
			$paramName = $param->getName();
			if (isset($context[$paramName])) $pass[] = $context[$paramName];
			elseif ($param->isDefaultValueAvailable()) $pass[] = $param->getDefaultValue();
			else throw new \InvalidArgumentException("Missing context " . $paramName);
		}
		return $handlerFunc->invokeArgs($handler, $pass);
	}
}
