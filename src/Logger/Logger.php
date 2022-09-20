<?php namespace Atomino2\Logger;

use Monolog\Level;
use Psr\Log\LoggerInterface;

abstract class Logger extends \Monolog\Logger {

	protected function stringify(\Stringable|string $payload): string { return (string)$payload; }

	public function debug(mixed $message, array $context = []): void { $this->log(Level::Debug, $message, $context); }
	public function info(mixed $message, array $context = []): void { $this->log(Level::Info, $message, $context); }
	public function notice(mixed $message, array $context = []): void { $this->log(Level::Notice, $message, $context); }
	public function warning(mixed $message, array $context = []): void { $this->log(Level::Warning, $message, $context); }
	public function error(mixed $message, array $context = []): void { $this->log(Level::Error, $message, $context); }
	public function critical(mixed $message, array $context = []): void { $this->log(Level::Critical, $message, $context); }
	public function alert(mixed $message, array $context = []): void { $this->log(Level::Alert, $message, $context); }
	public function emergency(mixed $message, array $context = []): void { $this->log(Level::Emergency, $message, $context); }

	public function log($level, mixed $message, array $context = []): void {
		parent::log($level, $this->stringify($message), $context);
		foreach ($this->subloggers as $logger) $logger->log($level, $message, $context);
	}

	/** @var Logger[] */
	private array $subloggers = [];
	public function addSubLogger(LoggerInterface $logger): static {
		$this->subloggers[] = $logger;
		return $this;
	}
}