<?php namespace Atomino2\Pipeline;

use DI\Container;

class PipelineFactory {
	public function __construct(private Container $di) {}
	public function builder(): PipelineBuilder{ return $this->di->make(PipelineBuilder::class); }
	public function sequence(): PipelineSequence{ return $this->di->make(PipelineSequence::class); }
}