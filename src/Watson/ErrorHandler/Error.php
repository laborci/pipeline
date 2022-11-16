<?php namespace Atomino2\Watson\ErrorHandler;

class Error {
	public function __construct(private int|string $errno, private string $errstr, private string $errfile, private int $errline, private array $trace = []) { }

	public function getErrfile(): string { return $this->errfile; }
	public function getErrline(): int { return $this->errline; }
	public function getErrno(): int|string { return $this->errno; }
	public function getErrstr(): string { return $this->errstr; }
	public function getTrace(): array { return $this->trace; }

	public function __toString(): string { return '[' . $this->errno . '] ' . $this->errstr . ' (' . $this->errfile . ' @ ' . $this->errline; }
}