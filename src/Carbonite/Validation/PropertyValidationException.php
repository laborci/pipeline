<?php namespace Atomino2\Carbonite\Validation;

class PropertyValidationException extends \Exception {

	/** @var string[] */
	private readonly array  $messages;
	private readonly string $property;
	public function getMessages(): array { return $this->messages; }
	public function getProperty(): string { return $this->property; }

	public function __construct(string $name, string ...$properties) {
		$this->message = sprintf("%s property validation error", $name);
		parent::__construct();
		$this->property = $name;
		$this->messages = $properties;
	}
}