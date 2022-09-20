<?php

use Atomino2\Pipeline\PipelineFactory_PHPDI;
use Atomino2\Pipeline\PipelineFactoryInterface;
use function DI\get;

return [
	PipelineFactoryInterface::class => get(PipelineFactory_PHPDI::class),
];
