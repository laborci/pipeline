<?php namespace Atomino2\Watson\Debug;

use Atomino2\Watson\Debug\DebugWriterInterface;
use Psr\Log\LoggerInterface;

class Debug {

	private static Debug|null $instance;

	public static function getInstance(): Debug|null {
		if (!isset(static::$instance)) return null;
		return static::$instance;
	}

	public function __construct() { static::$instance = $this; }

	/** @var LoggerInterface[] */
	private array $loggers = [];

	public function addLogger(LoggerInterface $logger): static {
		$this->loggers[] = $logger;
		return $this;
	}

	public function debug(mixed $payload): void {
		foreach ($this->loggers as $logger) $logger->debug($payload);
	}
}