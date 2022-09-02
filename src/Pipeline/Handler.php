<?php namespace Atomino2\Pipeline;

use Invoker\InvokerInterface;

abstract class Handler {
	private bool $isLastHandler = false;
	private Pipeline|null $pipeline = null;
	protected static function make($arguments): array {
		$reflection = new \ReflectionMethod(static::class, 'setup');
		$argumentNames = array_map(fn($arg) => $arg->name, $reflection->getParameters());
		return [static::class, array_combine($argumentNames, $arguments)];
	}
	protected function getContext(string|null $key = null): mixed { return $this->pipeline->getContext($key); }
	protected function setContext(string $key, mixed $value): void { $this->pipeline->getContext($key, $value); }
	protected function next() { return $this->pipeline->next(); }
	protected function break() { $this->pipeline->break(); }
	protected function isLast(): bool { return $this->isLastHandler; }
}