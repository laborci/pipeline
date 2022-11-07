<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Property\Type\ArrayTypeProperty;

class JsonProperty extends ArrayTypeProperty {
	protected function buildValue(mixed $value): array { return json_decode($value, true); }
	protected function storeValue(mixed $value): string { return json_encode($value); }
	protected function importValue(mixed $value): array { return $value; }
	protected function exportValue(mixed $value): array { return $value; }
}