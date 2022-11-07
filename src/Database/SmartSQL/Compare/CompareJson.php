<?php namespace Atomino2\Database\SmartSQL\Compare;

class CompareJson extends Compare {

	private mixed $value;
	private string $path;

	public function __construct(string $field, mixed $value, string $path = "$") {
		parent::__construct($field);
		$this->value = $value;
		$this->path = $path;
	}

	public function createSQL(): string {
		$sql = ($this->not ? " NOT " : " ") . "JSON_CONTAINS(" .$this->quoteEntity($this->field).",". $this->quoteValue($this->value) . ", " . $this->quoteValue($this->path) . ")";
		return $sql;
	}
}