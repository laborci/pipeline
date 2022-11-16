<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Persist;
use Atomino2\Carbonite\Carbonizer\Property;

class DateTimeProperty extends Property\Type\DateTimeTypeProperty {
	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		if ($field->columnDefault === 'CURRENT_TIMESTAMP' || str_contains($field->extra, 'CURRENT_TIMESTAMP')) {
			$this->access = Access::READ;
			$this->persist = Persist::NEVER;
		}
	}
}