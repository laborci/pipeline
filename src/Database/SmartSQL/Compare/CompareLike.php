<?php namespace Atomino2\Database\SmartSQL\Compare;

class CompareLike extends Compare {

	private string $value;
	private bool   $rev   = false;
	private bool   $glob  = false;
	private bool   $regex = false;

	public function __construct(string $field, string $value) {
		parent::__construct($field);
		$this->value = $value;
	}

	public function createSQL(): string {
		$operator = $this->regex ? "REGEXP" : "LIKE";
		$operator = " " . ($this->not ? "NOT " . $operator . " " : $operator) . " ";
		if ($this->rev) {
			$this->field = $this->glob ? strtr($this->field, ['*' => '%', '?' => '_']) : $this->field;
			$sql = $this->quoteValue($this->value) . $operator . $this->quoteEntity($this->field);
		} else {
			$this->value = $this->glob ? strtr($this->value, ['*' => '%', '?' => '_']) : $this->value;
			$sql = $this->quoteEntity($this->field) . $operator . $this->quoteValue($this->value);
		}
		return $sql;
	}

	public function glob(): static {
		$this->glob = true;
		return $this;
	}

	public function rev(): static {
		$this->rev = true;
		return $this;
	}

	public function regex(): static {
		$this->regex = true;
		return $this;
	}
}