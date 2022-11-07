<?php namespace Atomino2\Database\SmartSQL\Compare;

class CompareEquals extends Compare {

	private array $value;

	public function __construct(string $field, mixed ...$value) {
		parent::__construct($field);
		$this->value = $value;
	}

	public function createSQL(): string {
		$field = $this->quoteEntity($this->field);
		if (count($this->value) === 0) return "";
		if (count($this->value) === 1) {
			if (is_null($this->value[0])) return $field . ($this->not ? " IS NOT ": " IS ")." NULL ";
			else return $field . ($this->not ? " != ": " = "). $this->quoteValue($this->value[0]);
		}
		return $field . ($this->not ? " NOT IN ": " IN ")."(" . join(",", array_map(fn($item) => $this->quoteValue($item), $this->value)) . ")";
	}
}