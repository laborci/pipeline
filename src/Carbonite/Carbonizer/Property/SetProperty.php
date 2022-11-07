<?php namespace Atomino2\Carbonite\Carbonizer\Property;

use Atomino2\Carbonite\Carbonizer\Field;
use Atomino2\Carbonite\Carbonizer\Property;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class SetProperty extends Property\Type\ArrayTypeProperty {


	/*** @var string[] */
	protected array $options;
	public function getOptions(): array { return $this->options; }

	public function __construct(Field $field, int $persist, int $access, mixed $default) {
		parent::__construct($field, $persist, $access, $default);
		$this->options = $field->options;
	}

	protected function validator(): Validator|ChainedValidator { return parent::validator()->subset($this->options); }
	protected function buildValue(mixed $value): array { return explode(',', $value); }
	protected function storeValue(mixed $value): string { return join(',', $value); }
	protected function importValue(mixed $value): array { return $value; }
	protected function exportValue(mixed $value): array { return $value; }
}
