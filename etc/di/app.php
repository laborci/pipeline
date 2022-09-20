<?php

use App\ApplicationCli;
use App\ApplicationHTTP;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Config\Config;
use Atomino2\Util\PathResolver;
use DI\Container;
use function DI\factory;
use function DI\get;

class_alias(Config::class, \ApplicationConfig::class);
return [
	ApplicationInterface::class  => get(PHP_SAPI === 'cli' ? ApplicationCli::class : ApplicationHTTP::class),
	PathResolver::class          => factory(fn(ApplicationConfig $cfg) => new PathResolver($cfg("root"))),
	ApplicationConfig::class     => factory(fn(Container $container) => new Config($container->get("app-cfg"))),
	\Atomino2\Debug\Debug::class => null,
];

