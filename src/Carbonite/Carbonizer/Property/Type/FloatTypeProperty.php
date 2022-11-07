<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class FloatTypeProperty extends NumTypeProperty {
	protected function dataType(): string { return "float"; }
	protected function buildValue(mixed $value): float { return floatval($value); }
	protected function storeValue(mixed $value): float { return floatval($value); }
	protected function importValue(mixed $value): float { return floatval($value); }
	protected function exportValue(mixed $value): float { return floatval($value); }
	protected function defaultValue(mixed $default): float {
		if (is_null($default)) return 0;
		else return floatval($this->default);
	}
	protected function validator(): Validator|ChainedValidator {
		$validator = Validator::floatType();
		if ($this->signed) $validator->min(0);
		return $validator;
	}

}