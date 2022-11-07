<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class BoolTypeProperty extends Property {
	protected function dataType(): string { return "bool"; }
	protected function buildValue(mixed $value): bool { return boolval($value); }
	protected function storeValue(mixed $value): int { return intval($value); }
	protected function importValue(mixed $value): bool { return boolval($value); }
	protected function exportValue(mixed $value): bool { return boolval($value); }
	protected function validator(): Validator|ChainedValidator { return Validator::boolType(); }
	protected function defaultValue(mixed $default): bool {
		if (is_null($default)) return false;
		else return boolval($this->default);
	}
}