<?php namespace Atomino2\Pipeline;

use Invoker\InvokerInterface;

abstract class Handler {
	private bool $isLastHandler = false;
	private Pipeline|null $pipeline = null;
	protected static function make($arguments): array { return [static::class, array_values($arguments)]; }
	protected static function setupArgs(){return array_map(fn(\ReflectionParameter $param)=>$param->name, (new \ReflectionMethod( static::class.'::setup'))->getParameters());}
	protected function getContext(string|null $key = null): mixed { return $this->pipeline->getContext($key); }
	protected function setContext(string $key, mixed $value): void { $this->pipeline->setContext($key, $value); }
	protected function next() { return $this->pipeline->next(); }
	protected function break():never { $this->pipeline->break(); }
	protected function isLast(): bool { return $this->isLastHandler; }
}