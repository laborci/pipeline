<?php namespace Atomino2\Pipeline;

use DI\Container;
use Symfony\Component\HttpFoundation\ParameterBag;

class PipelineFactory_PHPDI implements PipelineFactoryInterface {
	public function __construct(private Container $di) { }
	public function builder(): PipelineBuilder { return $this->di->make(PipelineBuilder::class); }
	public function sequence(): PipelineSequence { return $this->di->make(PipelineSequence::class); }
	public function handler(string $handler): Handler { return $this->di->make($handler); }
	public function pipeline(ParameterBag $context, array $segments): Pipeline { return $this->di->make(Pipeline::class, ["context" => $context, "segments" => $segments]); }
}