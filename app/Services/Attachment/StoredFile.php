<?php namespace App\Services\Attachment;

use App\Services\Attachment\Constants\TableStorage;
use Atomino2\Util\Geometry\Dimension;
use Symfony\Component\HttpFoundation\File\File;

class StoredFile extends File {
	private int        $id;
	private bool       $image      = false;
	private string     $logicalPath;
	private ?string    $description;
	private null|array $tags;
	private ?Dimension $dimensions = null;

	public function getId(): int { return $this->id; }
	public function getDimensions(): ?Dimension { return $this->dimensions; }
	public function getTags(): ?array { return $this->tags; }
	public function getDescription(): ?string { return $this->description; }
	public function getLogicalPath(): string { return $this->logicalPath; }
	public function isImage(): bool { return $this->image; }

	public function __construct(array $row, private Storage $storage) {
		$this->id = $row[TableStorage::ID];
		$this->description = $row[TableStorage::DESCRIPTION];
		$this->tags = json_decode($row[TableStorage::TAGS]);
		$path = $storage->idToFullPath($this->id);
		$prefix = $storage->idToPrefix($this->id);
		$this->logicalPath = $storage->idToLogicalPath($this->id);
		if (!is_null($row[TableStorage::IMAGE])) {
			$this->image = true;
			$this->dimensions = Dimension::fromArray(json_decode($row[TableStorage::IMAGE], true));
		}
		$realFile = $path . '/' . $prefix . '.' . $row[TableStorage::FILE];
		parent::__construct($realFile);
	}
	public function setDescription(null|string $description) { $this->storage->setStoredFileDescription($this->id, $description); }
	public function setTags(null|array $tags) { $this->storage->setStoredFileTags($this->id, $tags); }
}