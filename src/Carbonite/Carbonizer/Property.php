<?php namespace Atomino2\Carbonite\Carbonizer;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Persist;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class Property {
	protected string $name;
	protected int $persist;
	protected int $access;
	protected bool $nullable;
	protected bool $primary;
	protected bool $virtual;
	protected mixed $default = null;
	protected Validator|ChainedValidator|null $validator = null;
	protected \Closure|null $validatorDecorator = null;

	public function isPrimary(): bool { return $this->primary; }
	public function isNullable(): bool { return $this->nullable; }
	public function getAccess(): int { return $this->access; }
	public function getName(): string { return $this->name; }
	public function getPersist(): int { return $this->persist; }
	public function getDefault(): mixed { return $this->createDefaultValue(); }

	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		$this->name = $field->columnName;
		$this->nullable = $field->isNullable === 'YES';
		$this->primary = $field->columnKey === 'PRI';
		$this->virtual = str_contains($field->extra, 'VIRTUAL');

		if ($this->primary || $this->virtual) {
			$this->persist = Persist::NEVER;
			$this->access = min(Access::READ, $access);
		} else {
			$this->persist = $persist;
			$this->access = $access;
		}
	}

	public final function validate(mixed $value): bool { return $this->createValidator()->validate($value); }
	public final function assert(mixed $value, ?string $name = null): void {
		$validator = $this->createValidator();
		$validatorName = $validator->getName();
		if (!is_null($name)) $validator->setName($name);
		$validator->assert($value);
		if (!is_null($name)) $validator->setName($validatorName);
	}
	public final function getDataType(): string { return ($this->isNullable() ? "?" : "") . $this->dataType(); }

	public final function build(mixed $value): mixed { return is_null($value) ? null : $this->buildValue($value); }
	public final function store(mixed $value): mixed { return is_null($value) ? null : $this->storeValue($value); }
	public final function import(mixed $value): mixed { return is_null($value) ? $this->createDefaultValue() : $this->importValue($value); }
	public final function export(mixed $value): mixed { return is_null($value) ? null : $this->exportValue($value); }


	protected function createValidator(): Validator|ChainedValidator {
		if (is_null($this->validator)) {
			$validator = $this->validator();
			if (!is_null($this->validatorDecorator)) ($this->validatorDecorator)($validator);
			$validator = $this->nullable ? Validator::nullable($validator) : $validator;
			$validator->setName($this->getName());
			$this->validator = $validator;
		}
		return $this->validator;
	}
	protected function createDefaultValue(): mixed {
		if ($this->default === null && $this->nullable) return null;
		return $this->defaultValue($this->default);
	}
	abstract protected function defaultValue(mixed $default): mixed;
	abstract protected function dataType(): string;
	abstract protected function validator(): Validator|ChainedValidator;

	abstract protected function buildValue(mixed $value): mixed;
	abstract protected function storeValue(mixed $value): mixed;
	abstract protected function importValue(mixed $value): mixed;
	abstract protected function exportValue(mixed $value): mixed;
}