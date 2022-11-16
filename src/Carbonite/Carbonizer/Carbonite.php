<?php namespace Atomino2\Carbonite\Carbonizer;

class Carbonite {
	private array     $properties  = [];
	private array     $relations   = [];
	private ?\Closure $initializer = null;

	public function getRelations(): array { return $this->relations; }

	public function __construct(public string $connection, public string $table, public bool $mutable = true) { }
	public function property(string $name, int $access = Access::READ_WRITE, int $persist = Persist::ALWAYS, mixed $default = null, \Closure|null $validator = null): static {
		$this->properties[$name] = [
			'access'    => $access,
			'persist'   => $persist,
			'default'   => $default,
			'validator' => $validator,
		];
		return $this;
	}

	public function relation(string $property, string $key, string $target, string|null $targetProperty = null): static {
		$this->relations[$property] = [
			'property'       => $property,
			'target'         => $target,
			'key'            => $key,
			'targetProperty' => $targetProperty,
		];
		return $this;
	}

	public function initializer(\Closure $initializer): static {
		$this->initializer = $initializer;
		return $this;
	}
	public function getInitializer(): ?\Closure { return $this->initializer; }


	public function getPropertyPreset(string $name): array {
		if (array_key_exists($name, $this->properties)) return $this->properties[$name];
		else return [
			'access'    => Access::READ_WRITE,
			'persist'   => Persist::ALWAYS,
			'default'   => null,
			'validator' => null,
		];
	}
}