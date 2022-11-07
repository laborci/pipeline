<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Property;

class FloatProperty extends Property\Type\FloatTypeProperty {
	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		$this->signed = !str_contains($field->columnType, 'UNSIGNED');
	}
}