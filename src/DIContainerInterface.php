<?php

namespace Atomino2;

use Psr\Container\ContainerInterface;

interface DIContainerInterface extends ContainerInterface {
	public function make($name, array $parameters = []);
}