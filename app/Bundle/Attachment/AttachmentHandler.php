<?php namespace App\Bundle\Attachment;


use Atomino2\Carbonite\Event\BeforeDelete;
use Atomino2\Carbonite\Event\OnDelete;
use Atomino2\Carbonite\PropertyHandler\PropertyHandler;
use function DI\string;

class AttachmentHandler extends PropertyHandler {

	private ?Storage $storage               = null;
	private array    $rawValue;

	public function __construct(private StorageFactory $storageFactory) {
	}

	public function addCollection(string $name, ?int $maxFileCount = null, ?int $maxFileSize = null, string|null $mimetype = null): static {
//		$this->storage->addCollection($name, $maxFileCount, $maxFileSize, $mimetype);
		return $this;
	}
	public function getCollections(): array {

		return $this->storage->getCollections();
	}
	public function getCollection(string $name): Collection|null {
		$this->setup();
		return $this->storage->getCollection($name);
	}

	public function beforeDelete(BeforeDelete $event) {
		$this->setup();
		$this->storage->removeAll();
	}

	private function setup() {
		if(!is_null($this->storage)) return;
		$this->storage = $this->storageFactory->createStorage();
		$this->storage->setup($this->getEntity(), $this->rawValue);
	}

	protected function getValue(): mixed { return $this->storage->isReady() ? $this->storage->export() : $this->rawValue; }

	protected function initialize(mixed $value) {

		$this->addEntityEventListener(BeforeDelete::class, fn($event) => $this->beforeDelete($event));
		$this->rawValue = $value;
	}
}