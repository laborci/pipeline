<?php namespace Atomino2\Pipeline;

use DI\Container;

class PipelineBuilder {
	private array $segments = [];

	public function __construct(private Container $di) { }

	public function pipe(string|array|Handler $handler, array $arguments = []): static {
		if (is_array($handler)) [$handler, $arguments] = $handler;
		if (!is_array($arguments)) $arguments = [$arguments];
		$this->segments[] = [$handler, $arguments];
		return $this;
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function exec(array $context = []) {
		/**
		 * @var Pipeline $pipeline
		 */
		$pipeline = $this->di->make(Pipeline::class, ["context" => $context, "segments" => $this->segments]);
		return $pipeline->next();
	}
}