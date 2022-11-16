<?php


use Atomino2\Mercury\HttpRequestLogger;
use Atomino2\Watson\ErrorHandler\ErrorLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use function DI\factory;

return [
	HttpRequestLogger::class => factory(fn(ApplicationConfig $cfg) => new HttpRequestLogger("HTTP-REQUEST", [(new RotatingFileHandler($cfg("general-log-path"), 10, Level::Info)),])),
	ErrorLogger::class       => factory(fn(ApplicationConfig $cfg) => new ErrorLogger("ERROR", [new RotatingFileHandler($cfg("general-log-path"), 10)])),
];