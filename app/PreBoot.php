<?php namespace App;

use App\Carbonite\User;
use Atomino2\Application\PreBootInterface;
use Atomino2\Carbonite\EntityEngine;
use Atomino2\Debug\Debug;
use Atomino2\ErrorHandler\ErrorCapture;
use DI\Container;

class PreBoot implements PreBootInterface {
	public function __construct(
		Container         $di,
		ErrorCapture|null $errorHandler,
		Debug|null        $debug
	) {

	}
}