<?php namespace App;

use Atomino2\Application\Config\Config;
use Atomino2\Application\PreBootInterface;
use Atomino2\Watson\Debug\Debug;
use Atomino2\Watson\ErrorHandler\ErrorCapture;
use DI\Container;


class PreBoot implements PreBootInterface {
	public function __construct(
		Container         $di,
		ErrorCapture|null $errorHandler,
		Debug|null        $debug
	) {	}
}