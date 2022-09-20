<?php namespace Atomino2\Mercury\Router;

use Atomino2\Pipeline\Exceptions\PipelineSequenceDepletedException;
use Atomino2\Pipeline\Handler;
use Atomino2\Pipeline\PipelineBuilder;
use Atomino2\Pipeline\PipelineFactoryInterface;
use Atomino2\Pipeline\PipelineSequence;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Router extends Handler {
	private PipelineSequence $sequence;

	public function __construct(private PipelineFactoryInterface $pipelineFactory) {
		$this->sequence = $this->pipelineFactory->sequence();
	}

	public function run() {
		$this->route();
		$context = $this->getContextBag();

		if (!$context->has("original-request")) $context->set("original-request", $context->get("request"));
		if (!$context->has("host-args")) $context->set("host-args", new ParameterBag());
		if (!$context->has("path-args")) $context->set("path-args", new ParameterBag());

		$this->sequence->context($context);

		try {
			return $this->sequence->exec();
		} catch (PipelineSequenceDepletedException $e) {
			$this->break();
		}
	}

	public function __invoke(
		string|null|array $path = null,
		string|null       $method = null,
		string|null       $host = null,
		string|null       $port = null,
		string|null       $scheme = null
	): PipelineBuilder {
		$pipeline = $this->pipelineFactory->builder()->pipe(Route::setup(...get_defined_vars()));
		$this->sequence->add($pipeline);
		return $pipeline;
	}
	abstract protected function route();
}
