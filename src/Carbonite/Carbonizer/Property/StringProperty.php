<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Property\Type\StringTypeProperty;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class StringProperty extends StringTypeProperty {

	private int $maxLength;
	public function getMaxLength(): int { return $this->maxLength; }
	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		$this->maxLength = $field->characterMaximumLength;
	}
	protected function validator(): Validator|ChainedValidator { return parent::validator()->length(0, $this->maxLength); }
}