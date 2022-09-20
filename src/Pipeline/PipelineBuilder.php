<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\EndOfPipelineException;
use DI\DependencyException;
use DI\NotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

class PipelineBuilder {
	private array $segments = [];

	private ParameterBag $context;

	public function __construct(private PipelineFactoryInterface $pipelineFactory) {
		$this->context = new ParameterBag();
	}

	public function pipe(string|array|PipelineBuilder|null $handler, ParameterBag|array|null $arguments = null): static {
		if (is_array($handler)) return $this->pipe(...$handler);
		if (is_null($handler)) return $this;
		if ($handler instanceof PipelineBuilder) {
			array_push($this->segments, ... $handler->segments);
			return $this;
		}
		if (is_null($arguments)) $arguments = [];
		if (!($arguments instanceof ParameterBag) && !is_array($arguments)) throw new \InvalidArgumentException("arguments must be a Parameterbag or Array");
		$this->segments[] = [$handler, $arguments];
		return $this;
	}

	public function context(string|ParameterBag $key, $value = null): static {
		if (is_string($key)) $this->context->set($key, $value);
		else $this->context->replace($key->all());
		return $this;
	}

	/**
	 * @return mixed
	 * @throws EndOfPipelineException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws \ReflectionException
	 */
	public function exec(): mixed {
		$pipeline = $this->pipelineFactory->pipeline($this->context, $this->segments);
		$result = $pipeline->next();
		return $result;
	}
}