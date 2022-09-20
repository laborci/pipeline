<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\EndOfPipelineException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Pipeline {
	public function __construct(
		private PipelineFactoryInterface $pipelineFactory,
		private ParameterBag $context,
		private array $segments
	) { }

	/**
	 * @throws \DI\NotFoundException
	 * @throws EndOfPipelineException
	 * @throws \ReflectionException
	 * @throws \DI\DependencyException
	 */
	public function next() {
		if (count($this->segments) === 0) throw new EndOfPipelineException();
		$segment = array_shift($this->segments);
		/** @var Handler $handler */
		/** @var ParameterBag|array $arguments */
		[$handler, $arguments] = $segment;
		$arguments = is_array($arguments) ? new ParameterBag($arguments) : $arguments;
		if (!is_subclass_of($handler, Handler::class)) throw new \InvalidArgumentException($handler . " is not a " . Handler::class);

		$handler = $this->makeHandler($handler, $arguments->all(), $this->context->all(), count($this->segments) === 0);
		return $handler->run();
	}

	public function break() { throw new Exceptions\BreakException(); }
	public function getContext(string|null $key = null) { return is_null($key) ? $this->context->all() : $this->context->get($key); }
	public function setContext(string $key, mixed $value) { $this->context->set($key, $value); }

	/**
	 * @throws \DI\NotFoundException
	 * @throws \ReflectionException
	 * @throws \DI\DependencyException
	 */
	private function makeHandler(string|Handler $handler, array $arguments, array $context, bool $lastHandler): Handler {
		if (is_string($handler)) {
			$handler = $this->pipelineFactory->handler($handler);
			return $this->makeHandler($handler, $arguments, $context, $lastHandler);
		}

		/* Inject */
		\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("pipeline", $this);
		if ($lastHandler) \Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("isLastHandler", $lastHandler);
		\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("arguments", new ParameterBag($arguments));
		\Closure::bind(fn($property, $value) => $this->$property = $value, $handler, Handler::class)("context", new ParameterBag($context));

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
