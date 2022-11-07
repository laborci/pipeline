<?php namespace Atomino2\Pipeline;

use Atomino2\Pipeline\Exceptions\BreakException;
use Atomino2\Pipeline\Exceptions\PipelineSequenceDepletedException;
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

	public function context(string|ParameterBag $key, $value = null): static {
		if (is_string($key)) $this->context->set($key, $value);
		else $this->context->replace($key->all());
		return $this;
	}

	public function exec():mixed {
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