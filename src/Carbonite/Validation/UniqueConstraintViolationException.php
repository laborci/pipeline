<?php namespace Atomino2\Carbonite\Validation;

class UniqueConstraintViolationException extends \Exception {

	/** @var string[] */
	private readonly array  $name;
	private readonly string $properties;
	public function getName(): array { return $this->name; }
	public function getProperties(): string { return $this->properties; }

	public function __construct(string $name, string ...$properties) {
		parent::__construct();
		$this->properties = $name;
		$this->name = $properties;
		if (count($properties) === 1) {
			$this->message = sprintf("[%s] property must be unique!", join(', ', $properties));
		} else {
			$this->message = sprintf("[%s] property set must be unique!", join(', ', $properties));
		}
	}
}