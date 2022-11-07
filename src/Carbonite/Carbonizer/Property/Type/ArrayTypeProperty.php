<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class ArrayTypeProperty extends Property {
	protected function dataType(): string { return "array"; }
	protected function defaultValue(mixed $default): array {
		if (is_null($default)) return [];
		else return is_array($this->default) ? $this->default : [$this->default];
	}
	protected function validator(): Validator|ChainedValidator { return Validator::arrayType(); }
}