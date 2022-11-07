<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

abstract class DateTimeTypeProperty extends Property {
	protected function dataType(): string { return \DateTime::class; }
	protected function defaultValue(mixed $default): \DateTime {
		if (is_null($default)) return new \DateTime("now");
		else return new \DateTime($default);
	}
	protected function validator(): Validator|ChainedValidator { return Validator::instance(\DateTime::class); }
	protected function buildValue(mixed $value): \DateTime { return new \DateTime($value); }
	protected function storeValue(mixed $value): string { return $value->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'); }
	protected function importValue(mixed $value): \DateTime { return \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $value); }
	protected function exportValue(mixed $value): string { return $value->format(\DateTimeInterface::ISO8601); }
}