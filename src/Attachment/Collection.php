<?php namespace Atomino2\Attachment;


use Atomino2\Carbonite\Entity;

class Collection {
	private readonly string  $name;
	private readonly ?array  $mimeType;
	private readonly string  $owner;
	private readonly ?int    $maxFileSize;
	private readonly ?int    $maxFileCount;
	public readonly Storage $storage;
	public readonly int     $uid;
	private array            $handlerCache = [];

	public function setStorage(Storage $storage): void { $this->storage = $storage; }
	public function getOwner(): string { return $this->owner; }
	public function getName(): string { return $this->name; }
	public function getMaxFileCount(): ?int { return $this->maxFileCount; }
	public function getMaxFileSize(): ?int { return $this->maxFileSize; }
	public function getMimeType(): ?array { return $this->mimeType; }

	public function __construct(string $owner, string $name, ?int $maxFileSize = null, ?int $maxFileCount = null, string|null|array $mimeType = null, ?int $uid = null) {
		$this->maxFileCount = $maxFileCount;
		$this->maxFileSize = $maxFileSize;
		$this->owner = $owner;
		$this->mimeType = is_array($mimeType) ? $mimeType : [$mimeType];
		$this->name = $name;
		$this->uid = $uid ?? hexdec(crc32($owner . '/' . $name));
	}

	public function getHandler(Entity $item): ?CollectionHandler {
		if (!$item->isExists()) return null;
		if (!array_key_exists($item->id, $this->handlerCache)) $this->handlerCache[$item->id] = new CollectionHandler($this, $item);
		return $this->handlerCache[$item->id];
	}

}