<?php namespace App;

use App\Router\MyRouter;
use Atomino2\Mercury\Middleware\Emitter;
use Atomino2\Mercury\Middleware\Measure;
use Atomino2\Pipeline\PipelineFactory;
use Symfony\Component\HttpFoundation\Request;

class ApplicationHTTP extends Application {

	public function __construct(
		private Request         $request,
		private PipelineFactory $pipelineFactory
	) {
	}

	public function run() {
		$this->pipelineFactory->builder()
		                      ->pipe(Emitter::class)
		                      ->pipe(Measure::class)
		                      ->pipe(MyRouter::class)
		                      ->exec(["request" => $this->request]);
	}
}
