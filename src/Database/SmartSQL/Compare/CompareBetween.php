<?php namespace Atomino2\Database\SmartSQL\Compare;

class CompareBetween extends Compare {

	private mixed $value1;
	private mixed $value2;

	public function __construct(string $field, mixed $value1, mixed $value2) {
		parent::__construct($field);
		$this->value1 = $value1;
		$this->value2 = $value2;
	}

	public function createSQL(): string {
		$sql = $this->quoteEntity($this->field) . ($this->not ? " NOT BETWEEN " : " BETWEEN ") . $this->quoteValue($this->value1) . " AND " . $this->quoteValue($this->value2);
		return $sql;
	}
}