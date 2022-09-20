<?php

namespace Atomino2\Mercury;

class RequestHandlerErrorException extends \Exception {
	public function __construct(
		string $message = "",
		int $code = 0,
		?\Throwable $previous = null,
		public int $statusCode = 500,
		public string $errorType = "",
		public string $errorMessage = "",
		public string $errorCode = ""
	) {
		parent::__construct($message, $code, $previous);
	}
}