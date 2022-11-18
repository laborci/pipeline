<?php namespace App\Services\Attachment;

use App\Services\Attachment\Constants\TableStorage;
use Atomino2\Util\Geometry\Dimension;
use Symfony\Component\HttpFoundation\File\File;

class StoredFile extends File {
	public readonly int        $id;
	private bool               $image = false;
	public readonly string     $logicalPath;
	public ?string             $description;
	public null|array          $tags;
	public readonly ?Dimension $dimensions;
	private readonly Storage   $storage;
	public readonly null|int   $orientationTransform;

	public function isImage(): bool { return $this->image; }

	public function __construct(array $row, Storage $storage) {
		$this->storage = $storage;
		$this->id = $row[TableStorage::ID];
		$this->description = $row[TableStorage::DESCRIPTION];
		$this->tags = json_decode($row[TableStorage::TAGS]);
		$path = $storage->idToFullPath($this->id);
		$prefix = $storage->idToPrefix($this->id);
		$this->logicalPath = $storage->idToLogicalPath($this->id);
		if (!is_null($image = $row[TableStorage::IMAGE])) {
			$this->image = true;
			$image = json_decode($image, true);
			$this->dimensions = Dimension::fromArray($image[TableStorage::IMAGE_SIZE]);
			$this->orientationTransform = $image[TableStorage::IMAGE_TRANSFORM];
		} else {
			$this->dimensions = null;
			$this->orientationTransform = null;
		}
		$realFile = $path . '/' . $prefix . '.' . $row[TableStorage::FILE];
		parent::__construct($realFile);
	}

	public function save() { $this->storage->saveStoredFile($this->id, $this->description, $this->tags); }
}