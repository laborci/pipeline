<?php

use Atomino2\Mercury\SmartResponder\SmartResponderEnv;
use Symfony\Component\HttpFoundation\Request;
use function DI\factory;

return [
	Request::class           => factory(fn() => Request::createFromGlobals()),
	SmartResponderEnv::class => factory(fn(ApplicationConfig $cfg) => new SmartResponderEnv(
		$cfg("smart-responder.namespaces"),
		$cfg("smart-responder.cache-path"),
		file_exists($file = $cfg("smart-responder.frontend-version-file")) ? filemtime($file) : 0,
		$cfg("smart-responder.debug")
	)),
];
