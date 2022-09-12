<?php

namespace App\Handler;

use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;

class WebHandler extends Handler {
	public static function setup(string $message = "Hello") { return static::make(func_get_args()); }
	public function handle(string $message) {
		return new Response($message);
	}

}