<?php

use App\ApplicationCli;
use App\ApplicationHTTP;
use App\PreBoot;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Application\PreBootInterface as PreBootInterfaceAlias;
use Atomino2\Config\Config;
use Atomino2\Util\CodeFinder;
use Atomino2\Util\PathResolver;
use Composer\Autoload\ClassLoader;
use DI\Container;
use function DI\factory;
use function DI\get;

class_alias(Config::class, \ApplicationConfig::class);
return [
	ApplicationInterface::class  => get(PHP_SAPI === 'cli' ? ApplicationCli::class : ApplicationHTTP::class),
	PathResolver::class          => factory(fn(ApplicationConfig $cfg) => new PathResolver($cfg("root"))),
	ApplicationConfig::class     => factory(fn(Container $container) => new Config($container->get("app-cfg"))),
	\Atomino2\Debug\Debug::class => null,
	PreBootInterfaceAlias::class => get(PreBoot::class),
	ClassLoader::class           => factory(fn(PathResolver $pathResolver) => require $pathResolver('vendor/autoload.php')),

];

