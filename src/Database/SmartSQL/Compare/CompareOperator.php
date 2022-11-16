<?php namespace Atomino2\Database\SmartSQL\Compare;

class CompareOperator extends Compare {

	const OPERATOR_GT  = ">";
	const OPERATOR_GTE = ">=";
	const OPERATOR_LT  = "<";
	const OPERATOR_LTE = "<=";

	private mixed  $value;
	private string $operator;

	public function __construct(string $field, mixed $value, string $operator) {
		parent::__construct($field);
		$this->value = $value;
		$this->operator = $operator;
	}

	public function createSQL(): string {
		$sql = $this->quoteEntity($this->field) . ($this->not ? " NOT " : " ") . $this->operator . $this->quoteValue($this->value);
		return $sql;
	}
}