<?php

use Atomino2\Watson\ErrorHandler\ErrorCapture;
use Atomino2\Watson\ErrorHandler\ErrorLogger;
use DI\Container;
use function DI\decorate;

return [
	ErrorCapture::class => decorate(fn(ErrorCapture $handler, Container $c) => $handler->addHandler($c->get(ErrorLogger::class))),
];