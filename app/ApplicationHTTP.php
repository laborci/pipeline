<?php namespace App;

use App\Mission\MissionRouter;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Mercury\FileServer\FileServer;
use Atomino2\Mercury\HttpRequestLogger;
use Atomino2\Mercury\Middleware\Cache;
use Atomino2\Mercury\Middleware\CatchException;
use Atomino2\Mercury\Middleware\Emitter;
use Atomino2\Mercury\Middleware\Measure;
use Atomino2\Mercury\Pipeline\PipeLine;
use Symfony\Component\HttpFoundation\Request;

class ApplicationHTTP implements ApplicationInterface {

	public function __construct(
		readonly Request                $request,
		readonly FileServer|null        $fileServer,
		readonly HttpRequestLogger|null $httpRequestLogger,
		readonly PipeLine               $pipeLine,
	) {
		$fileServer
			?->folder("/static/", "/public/")
			->file("/favicon.ico", "/public/kirk.jpg")
		;

		$httpRequestLogger?->info($this->request);

		$pipeLine
			->pipe(Emitter::class)
			->pipe(Measure::class)
			->pipe(CatchException::class)
			->pipe(Cache::class)
			->pipe(MissionRouter::class)
		($this->request);
	}
}
