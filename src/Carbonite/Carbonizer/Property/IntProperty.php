<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class IntProperty extends Property\Type\IntTypeProperty {

	protected string $dataType;

	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		$this->signed = !str_contains($field->columnType, 'UNSIGNED');
		$this->dataType = $field->dataType;
	}

	protected function validator(): Validator|ChainedValidator {
		$validator = parent::validator();
		if ($this->dataType === 'TINYINT') $this->signed ? $validator->min(-128)->max(127) : $validator->max(255);
		if ($this->dataType === 'SMALLINT') $this->signed ? $validator->min(-32768)->max(32767) : $validator->max(65535);
		if ($this->dataType === 'MEDIUMINT') $this->signed ? $validator->min(-8388608)->max(8388607) : $validator->max(16777215);
		if ($this->dataType === 'INT') $this->signed ? $validator->min(-2147483648)->max(2147483647) : $validator->max(4294967295);
		return $validator;
	}
}