<?php namespace Atomino2\Pipeline;

use Atomino2\DIContainerInterface;

class PipelineBuilderFactory {
	public function __construct(private DIContainerInterface $di) { }
	public function __invoke(array $context = []): PipelineBuilder { return $this->di->make(PipelineBuilder::class, ["context" => $context]); }
}