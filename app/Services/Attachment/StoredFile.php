<?php namespace App\Services\Attachment;

use Symfony\Component\HttpFoundation\File\File;

class StoredFile extends File {
	private int        $id;
	private bool       $image = false;
	private int        $height;
	private int        $width;
	private string     $logicalPath;
	private ?string    $description;
	private null|array $tags;

	public function getId(): int { return $this->id; }
	public function getWidth(): int { return $this->width; }
	public function getHeight(): int { return $this->height; }
	public function getTags(): ?array { return $this->tags; }
	public function getDescription(): ?string { return $this->description; }
	public function getLogicalPath(): string { return $this->logicalPath; }
	public function isImage(): bool { return $this->image; }

	public function __construct(array $row, private Storage $storage) {
		$this->id = $row['id'];
		$this->description = $row["description"];
		$this->tags = json_decode($row["tags"]);
		$path = $storage->idToFullPath($this->id);
		$prefix = $storage->idToPrefix($this->id);
		$this->logicalPath = $storage->idToLogicalPath($this->id);
		if (!is_null($row['imageDimensions'])) {
			$this->image = true;
			$imageProps = json_decode($row['imageDimensions'], true);
			$this->width = $imageProps['width'];
			$this->height = $imageProps['height'];
		}
		$realFile = $path . '/' . $prefix . '.' . $row['file'];
		parent::__construct($realFile);
	}
	public function setDescription(null|string $description) { $this->storage->setStoredFileDescription($this->id, $description); }
	public function setTags(null|array $tags) { $this->storage->setStoredFileTags($this->id, $tags); }
}