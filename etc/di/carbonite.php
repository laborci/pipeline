<?php

use Atomino2\Carbonite\Carbonizer\Carbonizer;
use Atomino2\Util\CodeFinder;
use Atomino2\Util\PathResolver;
use DI\Container;
use function DI\factory;

return [
	Carbonizer::class => factory(fn(Container $di, CodeFinder $codeFinder, PathResolver $pathResolver) => new Carbonizer($di, $codeFinder, $pathResolver, \App\Carbonite::class, \App\Carbonite\Store::class, \App\Carbonite\Machine::class)),
];

