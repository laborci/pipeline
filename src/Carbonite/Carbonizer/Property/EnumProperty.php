<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Property\Type\StringTypeProperty;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class EnumProperty extends StringTypeProperty {

	/*** @var string[] */
	protected array $options;
	public function getOptions(): array { return $this->options; }

	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		$this->options = $field->options;
	}

	protected function validator(): Validator|ChainedValidator { return Validator::stringType()->in($this->options); }

	protected function defaultValue(mixed $default): string {
		if (is_null($default)) return $this->options[0];
		else return strval($this->default);
	}
}