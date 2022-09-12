<?php namespace Atomino2\Pipeline;

use DI\Container;

class PipelineFactory {
	public function __construct(private Container $di) {}
	public function builder(): PipelineBuilder{ return $this->di->make(PipelineBuilder::class); }
	public function runner(): PipelineRunner{ return $this->di->make(PipelineRunner::class); }
}