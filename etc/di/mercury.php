<?php

use Atomino2\Mercury\SmartResponder\SmartResponderEnv;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use function DI\factory;

return [
	Request::class           => factory(fn() => Request::createFromGlobals()),
	SmartResponderEnv::class => factory(fn(ApplicationConfig $cfg) => new SmartResponderEnv(
		$cfg("mercury.smart-responder.namespaces"),
		$cfg("mercury.smart-responder.cache-path"),
		file_exists($file = $cfg("mercury.smart-responder.frontend-version-file")) ? filemtime($file) : 0,
		$cfg("mercury.smart-responder.debug")
	)),
	CacheInterface::class    => factory(fn(ApplicationConfig $cfg) => new FilesystemAdapter('', 60, $cfg("mercury.middlewares.cache.path"))),
];
