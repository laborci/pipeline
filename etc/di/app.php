<?php

use App\ApplicationCli;
use App\ApplicationHTTP;
use Atomino2\Application\ApplicationInterface;
use function DI\get;

return [ApplicationInterface::class => get(PHP_SAPI === 'cli' ? ApplicationCli::class : ApplicationHTTP::class)];

