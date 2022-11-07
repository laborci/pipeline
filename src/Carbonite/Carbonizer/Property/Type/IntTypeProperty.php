<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class IntTypeProperty extends NumTypeProperty {
	protected function dataType(): string { return "int"; }
	protected function buildValue(mixed $value): int { return intval($value); }
	protected function storeValue(mixed $value): int { return intval($value); }
	protected function importValue(mixed $value): int { return intval($value); }
	protected function exportValue(mixed $value): int { return intval($value); }
	protected function defaultValue(mixed $default): int {
		if (is_null($default)) return 0;
		else return intval($this->default);
	}
	protected function validator(): Validator|ChainedValidator {
		$validator = Validator::intType();
		if ($this->signed) $validator->min(0);
		return $validator;
	}
}