<?php namespace App\Bundle\Attachment;

use Atomino2\Carbonite\Entity;

class Storage {

	private string $imgUrl;
	private string $fileUrl;
	private string $path;
	/** @var File[] */
	private array       $files = [];
	private null|Entity $item  = null;
	public function __construct(string $path, string $fileUrl, string $imgUrl) {
		$this->path = $path;
		$this->fileUrl = $fileUrl;
		$this->imgUrl = $imgUrl;
	}

	public function isReady(): bool { return !is_null($this->item); }

	/** @var Collection[] */
	private array $collections = [];
	public function getCollections(): array { return array_keys($this->collections); }
	public function hasCollection(string $name): bool { return array_key_exists($name, $this->collections); }
	public function getCollection(string $name): Collection|null { return array_key_exists($name, $this->collections) ? $this->collections[$name] : null; }

	public function addCollection(string $name, ?int $maxFileCount = null, ?int $maxFileSize = null, string|null $mimetype = null): void {
		$this->collections[$name] = new Collection($this, $name, $maxFileCount, $maxFileSize, $mimetype);
	}

	public function setup(Entity $item, mixed $value): void {
		if ($this->isReady()) return;
		$this->item = $item;
		$class = (new \ReflectionClass($item))->getShortName();
		$id = wordwrap(str_pad($item->id, 9, '0', STR_PAD_LEFT), 3, '/', true);
		$this->path .= '/' . $class . '/' . $id . '/';
		$this->fileUrl .= '/' . $class . '/' . $id . '/';
		$this->setValue($value);
	}

	public function hasFile($name): bool { return array_key_exists($name, $this->files); }

	private function setValue(mixed $value): void {
		if (!array_key_exists('files', $value)) $value['files'] = [];
		if (!array_key_exists('collections', $value)) $value['collections'] = [];

		foreach ($value['files'] as $name => $fileData) $this->files[$name] = new File($name, $fileData);

		foreach ($this->collections as $collectionName => $collection) {
			if (array_key_exists($collectionName, $value['collections'])) {
				foreach ($collection as $fileName) $collection->addFile($fileName);
			}
		}

		$this->cleanUp();
	}

	public function purgeFile(string $fileName): void {
		foreach ($this->collections as $collection) if ($collection->hasFile($fileName)) return;
		$this->removeFile($fileName);
	}

	private function cleanUp(): void { foreach ($this->files as $fileName => $file) $this->purgeFile($fileName); }

	private function removeFile(int|string $fileName) {
		unset($this->files[$fileName]);
		// TODO: delete file from filesystem
	}
	public function removeAll(): void {
		foreach ($this->collections as $collection) {
			foreach ($collection->getAttachments() as $attachment) $attachment->delete();
		}
	}

	public function export(): array {
		// TODO: return exported value
		return [];
	}
	public function getFile($fileName): File { return $this->files[$fileName]; }

}