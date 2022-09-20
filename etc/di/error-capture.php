<?php

use Atomino2\ErrorHandler\ErrorCapture;
use Atomino2\ErrorHandler\ErrorLogger;
use DI\Container;
use function DI\decorate;

return [
	ErrorCapture::class => decorate(fn(ErrorCapture $handler, Container $c) => $handler->addHandler($c->get(ErrorLogger::class))),
];