<?php

use App\ApplicationCli;
use App\ApplicationHTTP;
use App\PreBoot;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Application\PreBootInterface as PreBootInterfaceAlias;
use Atomino2\Application\Config\Config;
use Atomino2\Util\CodeFinder;
use Atomino2\Util\PathResolver;
use Composer\Autoload\ClassLoader;
use DI\Container;
use function DI\factory;
use function DI\get;

return [
	PathResolver::class                 => factory(fn(ApplicationConfig $cfg) => new PathResolver($cfg("root"))),
	ApplicationConfig::class            => factory(fn(Container $container) => new Config($container->get("app-cfg"))),
	\Atomino2\Watson\Debug\Debug::class => null,
	PreBootInterfaceAlias::class        => get(PreBoot::class),
	ClassLoader::class                  => factory(fn(PathResolver $pathResolver) => require $pathResolver('vendor/autoload.php')),
];

