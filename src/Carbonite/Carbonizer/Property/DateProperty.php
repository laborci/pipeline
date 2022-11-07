<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Property\Type\DateTimeTypeProperty;

class DateProperty extends DateTimeTypeProperty {
	protected function defaultValue(mixed $default): \DateTime {
		if (is_null($default)) return new \DateTime("today midnight");
		else return new \DateTime($default);
	}
	protected function buildValue(mixed $value): \DateTime { return (new \DateTime($value))->setTime(0, 0, 0, 0); }
	protected function storeValue(mixed $value): string { return $value->format('Y-m-d'); }
	protected function importValue(mixed $value): \DateTime { return new \DateTime($value); }
	protected function exportValue(mixed $value): string { return $value->format(\DateTime::ATOM); }
}