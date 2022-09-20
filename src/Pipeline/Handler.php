<?php namespace Atomino2\Pipeline;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 *    public static function setup(string $message): array { return parent::setup(get_defined_vars()); }
 * @method static setup(array|null $arguments = null)
 */
abstract class Handler {

	private ParameterBag $arguments;
	private ParameterBag $context;
	private Pipeline|null $pipeline = null;
	private bool $isLastHandler = false;

	public static function __callStatic(string $name, array $arguments) { return [static::class, count($arguments) ? $arguments[0] : null]; }

	abstract public function run();

	protected function ctx(string $key): mixed { return $this->context->get($key); }
	protected function arg(string $key) { return $this->arguments->get($key); }

	protected function getContextBag(): ParameterBag { return $this->pipeline->getContextBag(); }
	protected function getContext(string|null $key = null): mixed { return $this->pipeline->getContext($key); }
	protected function setContext(string $key, mixed $value): void { $this->pipeline->setContext($key, $value); }

	protected function next() { return $this->pipeline->next(); }
	protected function break(): never { $this->pipeline->break(); }
	protected function isLast(): bool { return $this->isLastHandler; }
}
