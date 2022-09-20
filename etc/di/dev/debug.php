<?php

use Atomino2\Mercury\HttpRequestLogger;
use Atomino2\Debug\ANSIIFormatter\ErrorFormatter;
use Atomino2\Debug\ANSIIFormatter\HttpRequestFormatter;
use Atomino2\Debug\ANSIIFormatter\UserFormatter;
use Atomino2\Debug\Debug;
use Atomino2\Debug\Logger\HttpLogger;
use Atomino2\Debug\Logger\StdOutLogger;
use Atomino2\ErrorHandler\ErrorLogger;
use Atomino2\Mercury\Middleware\Measure;
use Atomino2\Pipeline\PipelineBuilder;
use Atomino2\Pipeline\PipelineFactoryInterface;
use DI\Container;
use function DI\decorate;
use function DI\factory;

class_alias(PipelineBuilder::class, DebugPipeline::class);


return [
	DebugPipeline::class  => factory(fn(PipelineFactoryInterface $factory) => $factory->builder()->pipe(Measure::class)),
	ErrorFormatter::class => factory(fn(ApplicationConfig $cfg) => new ErrorFormatter($cfg("root"))),

	HttpRequestLogger::class => decorate(fn(HttpRequestLogger $logger, Container $di) => $logger->addSubLogger(new StdOutLogger(new HttpRequestFormatter()))),
	ErrorLogger::class       => decorate(fn(ErrorLogger $logger, Container $di) => $logger->addSubLogger(new StdOutLogger($di->get(ErrorFormatter::class)))),
	Debug::class             => factory(fn() => (new Atomino2\Debug\Debug())
		->addLogger(new StdOutLogger(new UserFormatter()))
		->addLogger(new HttpLogger(new UserFormatter(), "http://127.0.0.1:9999"))
	),
];

