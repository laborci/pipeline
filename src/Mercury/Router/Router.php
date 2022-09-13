<?php namespace Atomino2\Mercury\Router;

use Atomino2\Mercury\Router\Route;
use Atomino2\Pipeline\Exceptions\PipelineSequenceDepletedException;
use Atomino2\Pipeline\Handler;
use Atomino2\Pipeline\PipelineBuilder;
use Atomino2\Pipeline\PipelineFactory;
use Atomino2\Pipeline\PipelineSequence;
use Symfony\Component\HttpFoundation\Request;

abstract class Router extends Handler {
	private PipelineSequence $sequence;

	public function __construct(private PipelineFactory $pipelineFactory) {
		$this->sequence = $this->pipelineFactory->sequence();
	}
	public function handle(Request $request) {
		$this->route();
		try {
			return $this->sequence->exec(["request" => $request]);
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
		$pipeline = $this->pipelineFactory->builder()->pipe(Route::class, [$method, $path, $host, $port, $scheme]);
		$this->sequence->add($pipeline);
		return $pipeline;
	}
	abstract protected function route();
}
