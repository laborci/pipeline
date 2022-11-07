<?php namespace App\Bundle\Attachment;


use Atomino2\Carbonite\PropertyHandler\PropertyHandler;

class AttachmentHandler extends PropertyHandler {
	/** @var AttachmentCollection[] */
	private array $collections = [];
	private array $collectionDescriptors = [];

	public function __construct() { }
	public function addCollection(string $name, ?int $maxFileSize = null, ?int $maxFileCount = null): static {
		return $this;
	}
	public function getCollection(string $name): AttachmentCollection { return $this->collections[$name]; }

	protected function getValue(): mixed {
		return null;
	}

	protected function initialize(mixed $value) {
		// TODO: Implement initialize() method.
	}
}