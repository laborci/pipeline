<?php namespace Atomino2\Watson\Debug\Logger;

use Atomino2\Watson\Debug\FormatterInterface;
use Monolog\Level;
use Psr\Log\LoggerInterface;

abstract class AbstractLogger implements LoggerInterface {

	protected FormatterInterface $formatter;
	public function __construct(FormatterInterface $formatter) { $this->formatter = $formatter; }

	public function debug(mixed $message, array $context = []): void { $this->log(Level::Debug, $message, $context); }
	public function info(mixed $message, array $context = []): void { $this->log(Level::Info, $message, $context); }
	public function notice(mixed $message, array $context = []): void { $this->log(Level::Notice, $message, $context); }
	public function warning(mixed $message, array $context = []): void { $this->log(Level::Warning, $message, $context); }
	public function error(mixed $message, array $context = []): void { $this->log(Level::Error, $message, $context); }
	public function critical(mixed $message, array $context = []): void { $this->log(Level::Critical, $message, $context); }
	public function alert(mixed $message, array $context = []): void { $this->log(Level::Alert, $message, $context); }
	public function emergency(mixed $message, array $context = []): void { $this->log(Level::Emergency, $message, $context); }
	public function log($level, mixed $message, array $context = []): void { $this->write($this->formatter->format($message)); }
	abstract function write($message);
}

