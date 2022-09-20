<?php namespace Atomino2\ErrorHandler;


class ErrorCapture {
	/** @var ErrorHandlerInterface[] */
	private array $handlers = [];

	public function __construct() {
		register_shutdown_function(fn() => $this->fatalError());
		set_exception_handler(fn(\Throwable $exception) => $this->exception($exception));
		set_error_handler(fn(int $errno, string $errstr, string $errfile, int $errline) => $this->error($errno, $errstr, $errfile, $errline), E_ALL);
	}

	public function addHandler(ErrorHandlerInterface $handler): static {
		$this->handlers[] = $handler;
		return $this;
	}

	private function exception(\Throwable $exception) {
		$line = $exception->getLine();
		$file = $exception->getFile();
		$message = $exception->getMessage() . ' (' . $exception->getCode() . ')';
		$trace = $exception->getTrace();
		$type = get_class($exception);
		if ($exception instanceof \ErrorException) {
			$ftrace = $trace[0];
			array_shift($trace);
			$type = $this->errorType(array_key_exists('args', $ftrace) ? $ftrace['args'][0] : E_ERROR);
		}
		foreach ($this->handlers as $handler) $handler->handle(new Error($type, $message, $file, $line, $trace));
	}

	private function error(int $errno, string $errstr, string $errfile, int $errline) {
		foreach ($this->handlers as $handler) $handler->handle(new Error($this->errorType($errno), $errstr, $errfile, $errline));
	}

	private function fatalError() {
		if (!is_null($error = error_get_last())) {
			$this->error($error['type'], $error['message'], $error['file'], $error['line']);
			exit;
		}
	}

	private function errorType($type) {
		return match ($type) {
			E_ERROR => 'ERROR',
			E_WARNING => 'WARNING',
			E_PARSE => 'PARSE',
			E_NOTICE => 'NOTICE',
			E_CORE_ERROR => 'CORE_ERROR',
			E_CORE_WARNING => 'CORE_WARNING',
			E_COMPILE_ERROR => 'COMPILE_ERROR',
			E_COMPILE_WARNING => 'COMPILE_WARNING',
			E_USER_ERROR => 'USER_ERROR',
			E_USER_WARNING => 'USER_WARNING',
			E_USER_NOTICE => 'USER_NOTICE',
			E_STRICT => 'STRICT',
			E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
			E_DEPRECATED => 'DEPRECATED',
			E_USER_DEPRECATED => 'USER_DEPRECATED'
		};
	}
}