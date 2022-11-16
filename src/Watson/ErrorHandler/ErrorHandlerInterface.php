<?php namespace Atomino2\Watson\ErrorHandler;

interface ErrorHandlerInterface {
	public function handle(Error $error);
}