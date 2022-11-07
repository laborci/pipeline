<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class StringTypeProperty extends Property {
	protected function dataType(): string { return "string"; }
	protected function buildValue(mixed $value): string { return strval($value); }
	protected function storeValue(mixed $value): string { return strval($value); }
	protected function importValue(mixed $value): string { return strval($value); }
	protected function exportValue(mixed $value): string { return strval($value); }
	protected function defaultValue(mixed $default): string {
		if (is_null($default)) return "";
		else return strval($this->default);
	}
	protected function validator(): Validator|ChainedValidator { return Validator::stringType(); }
}