<?php namespace Atomino2\Watson\ErrorHandler;

use Atomino2\Watson\Logger\Logger;
use Monolog\Level;

class ErrorLogger extends Logger implements ErrorHandlerInterface {
	public function handle(Error $error) { $this->log(Level::Error, $error); }
}