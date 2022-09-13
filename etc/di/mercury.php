<?php

use Symfony\Component\HttpFoundation\Request;
use function DI\factory;

return [
	Request::class => factory(fn() => Request::createFromGlobals()),
];
