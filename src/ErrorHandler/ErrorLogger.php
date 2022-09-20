<?php namespace Atomino2\ErrorHandler;

use Atomino2\Logger\Logger;
use Monolog\Level;

class ErrorLogger extends Logger implements ErrorHandlerInterface {
	public function handle(Error $error) { $this->log(Level::Error, $error); }
}