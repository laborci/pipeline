<?php namespace Atomino2\Carbonite\Validation;

class EntityValidationException extends \Exception {

	/** @var string[] */
	private array $messages;
	public function getMessages(): array { return $this->messages; }
	public function __construct(string ...$messages) {
		$this->message = $messages[0];
		parent::__construct();
		$this->messages = $messages;
	}
}