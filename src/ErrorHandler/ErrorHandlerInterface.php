<?php namespace Atomino2\ErrorHandler;

interface ErrorHandlerInterface {
	public function handle(Error $error);
}