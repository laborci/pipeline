<?php namespace Atomino2\Pipeline;

use Symfony\Component\HttpFoundation\ParameterBag;

interface PipelineFactoryInterface {
	public function builder(): PipelineBuilder;
	public function sequence(): PipelineSequence;
	public function handler(string $handler): Handler;
	public function pipeline(ParameterBag $context, array $segments): Pipeline;
}