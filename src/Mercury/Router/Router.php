<?php namespace Atomino2\Mercury\Router;

use Atomino2\Mercury\Router\Route;
use Atomino2\Pipeline\Exceptions\PipelineRunnerDeflatedException;
use Atomino2\Pipeline\Handler;
use Atomino2\Pipeline\PipelineBuilder;
use Atomino2\Pipeline\PipelineFactory;
use Atomino2\Pipeline\PipelineRunner;
use Symfony\Component\HttpFoundation\Request;

abstract class Router extends Handler {
	private PipelineRunner $runner;

	public function __construct(private PipelineFactory $pipelineFactory) {
		$this->runner = $this->pipelineFactory->runner();
	}
	public function handle(Request $request) {
		$this->route();
		try {
			return $this->runner->exec(["request" => $request]);
		} catch (PipelineRunnerDeflatedException $e) {
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
		$this->runner->add($pipeline);
		return $pipeline;
	}
	abstract protected function route();
}
