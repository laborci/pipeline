<?php

use Symfony\Component\HttpFoundation\Request;
use function DI\factory;

return [
	Request::class                           => factory(fn() => Request::createFromGlobals()),
//	\Atomino2\Pipeline\PipelineRunner::class => \DI\create(\Atomino2\Pipeline\PipelineRunner::class),
		\Atomino2\Pipeline\PipelineRunner::class=>\DI\factory(fn(\DI\Container $di)=>new \Atomino2\Pipeline\PipelineRunner($di)),
];
