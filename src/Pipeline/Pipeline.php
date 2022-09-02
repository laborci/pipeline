<?php namespace Atomino2\Pipeline;


use Atomino2\DIContainerInterface;
use Atomino2\Pipeline\Exceptions\EndOfPipelineException;

class Pipeline{
	public function __construct(private readonly DIContainerInterface $di, private array $context, private array $segments) { }

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
		$handler = $this->makeHandler($handler, count($this->segments) === 0);
		return $this->runHandler($handler, $arguments);
	}

	public function break() { throw new Exceptions\BreakException(); }
	public function getContext(string|null $key = null) { return is_null($key) ? $this->context : (array_key_exists($key, $this->context) ? $this->context[$key] : null); }
	public function setContext(string $key, mixed $value) { $this->context["key"] = $value; }

	/**
	 * @throws \DI\NotFoundException
	 * @throws \ReflectionException
	 * @throws \DI\DependencyException
	 */
	private function makeHandler(string|Handler $handler, bool $lastHandler): Handler {
		if (is_string($handler)) {
			$handler = $this->di->make($handler);
			return $this->makeHandler($handler, $lastHandler);
		}

		/* Inject pipeline */
		\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("pipeline", $this);

		/* Inject isLastHandler*/
		if ($lastHandler) \Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("isLastHandler", $lastHandler);

		/* Inject Context properties*/
		$attrs = Context::all(new \ReflectionClass($handler));
		foreach ($attrs as $property => $attr) {
			$contextName = is_null($attr->name) ? $property : $attr->name;
			if (array_key_exists($contextName, $this->context)) {
				\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, $handler)($property, $this->context[$contextName]);
			}
		}
		return $handler;
	}

	/**
	 * @throws \ReflectionException
	 */
	private function runHandler(Handler $handler, array $arguments) {
		$handlerFunc = (new \ReflectionMethod($handler, "handle"));
		$pass = [];
		foreach ($handlerFunc->getParameters() as $param) {
			if (isset($arguments[$param->getName()])) $pass[] = $arguments[$param->getName()];
			else $pass[] = $param->getDefaultValue();
		}
		return $handlerFunc->invokeArgs($handler, $pass);
	}
}
