<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\BreakException;
use Atomino2\Pipeline\Exceptions\PipelineSequenceDepletedException;
use DI\DependencyException;
use DI\NotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

class PipelineSequence {

	private ParameterBag $context;

	public function __construct() {
		$this->context = new ParameterBag();
	}

	/** @var PipelineBuilder[] */
	private array $pipelineBuilders = [];

	public function add(PipelineBuilder $builder): static {
		$this->pipelineBuilders[] = $builder;
		return $this;
	}

	public function hasContext($key): bool { return $this->context->has($key); }

	public function context(string|ParameterBag $key, $value): static {
		if (is_string($key)) $this->context->set($key, $value);
		else $this->context->replace($key->all());
		return $this;
	}

	/**
	 * @throws DependencyException
	 * @throws PipelineSequenceDepletedException
	 * @throws NotFoundException
	 */
	public function exec() {
		foreach ($this->pipelineBuilders as $builder) {
			try {
				$builder->context($this->context);
				return $builder->exec();
			} catch (BreakException $e) {
				// do nothing it just broke the current pipeline
			}
		}
		throw new PipelineSequenceDepletedException();
	}
}