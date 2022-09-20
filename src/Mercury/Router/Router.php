<?php namespace Atomino2\Mercury\Router;

use Atomino2\Pipeline\Exceptions\PipelineSequenceDepletedException;
use Atomino2\Pipeline\Handler;
use Atomino2\Pipeline\PipelineBuilder;
use Atomino2\Pipeline\PipelineFactoryInterface;
use Atomino2\Pipeline\PipelineSequence;

abstract class Router extends Handler {
	private PipelineSequence $sequence;

	public function __construct(private PipelineFactoryInterface $pipelineFactory) {
		$this->sequence = $this->pipelineFactory->sequence();
	}

	public function run() {
		$this->route();

		$request = $this->ctx("request");
		$originalRequest = $this->ctx("original-request") ?: $request;

		$this->sequence->context("original-request", $originalRequest);
		$this->sequence->context("request", $request);

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
