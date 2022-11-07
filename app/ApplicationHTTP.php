<?php namespace App;

use App\Mission\MissionRouter;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Carbonite\Entity;
use Atomino2\Debug\Debug;
use Atomino2\ErrorHandler\ErrorCapture;
use Atomino2\Mercury\FileServer\FileServer;
use Atomino2\Mercury\HttpRequestLogger;
use Atomino2\Mercury\Middleware\CatchException;
use Atomino2\Mercury\Middleware\Emitter;
use Atomino2\Pipeline\PipelineFactoryInterface;
use DI\Container;
use Symfony\Component\HttpFoundation\Request;

class ApplicationHTTP implements ApplicationInterface {

	public function __construct(
		private readonly Request                  $request,
		private readonly PipelineFactoryInterface $pipelineFactory,
		private readonly FileServer|null          $fileServer,
		private readonly \DebugPipeline|null      $debugPipeline,
		private readonly HttpRequestLogger|null   $httpRequestLogger,
	) {
		$this->fileServer
			?->folder("/static/", "/public/")
			->file("/favicon.ico", "/public/kirk.jpg")
		;

		$this->httpRequestLogger?->info($this->request);

		$this->pipelineFactory
			->builder()
			->pipe(Emitter::class)
			->pipe(CatchException::setup(true))
			->pipe($this->debugPipeline)
			->pipe(MissionRouter::class)
			->context("request", $this->request)
			->exec()
		;
	}
}
