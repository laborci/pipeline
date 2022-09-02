<?php namespace Atomino2\Pipeline;

use Atomino2\DIContainerInterface;

class PipelineBuilder {
	private array $segments = [];

	public function __construct(private DIContainerInterface $di, private array $context = []) { }

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
		$context = array_merge($this->context, $context);
		/**
		 * @var Pipeline $pipeline
		 */
		$pipeline = $this->di->make(Pipeline::class, ["context" => $context, "segments" => $this->segments]);
		return $pipeline->next();
	}
}