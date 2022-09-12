<?php

use Atomino2\Config\Config;
use DI\Container;
use function DI\factory;

class_alias(Config::class, \ApplicationConfig::class);

return [ApplicationConfig::class => factory(fn(Container $container) => new Config($container->get("app-cfg")))];