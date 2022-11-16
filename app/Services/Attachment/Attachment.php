<?php namespace App\Services\Attachment;

class Attachment {

	public readonly string      $file;
	public readonly string      $mimeType;
	public readonly int         $id;
	public readonly int         $storageId;
	public readonly int         $ownerId;
	public readonly int         $collectionId;
	public readonly int         $position;
	public readonly array|null  $img;
	public readonly string|null $title;
	private bool                $image = false;
	public readonly ?int        $width;
	public readonly ?int        $height;

	public function isImage(): bool { return $this->image; }

	public function __construct(private readonly CollectionHandler $collectionHandler, array $row) {
		$this->id = $row['id'];
		$this->file = $row['file'];
		$this->collectionId = $row['collectionId'];
		$this->img = is_null($row['img']) ? null : json_decode($row['img'], true);
		$this->mimeType = $row['mimeType'];
		$this->ownerId = $row['ownerId'];
		$this->position = $row['position'];
		$this->storageId = $row['storageId'];
		$this->title = $row['title'];

		if (!is_null($row['imageDimensions'])) {
			$this->image = true;
			$imageProps = json_decode($row['imageDimensions'], true);
			$this->width = $imageProps['width'];
			$this->height = $imageProps['height'];
		} else {
			$this->width = $this->height = null;
		}
	}

	public function setPosition(int|bool $position): void { $this->collectionHandler->setAttachmentPosition($this->id, $position); }
	public function setTitle(string|null $title): void { $this->collectionHandler->setAttachmentTitle($this->id, $title); }
	public function setImg(string|null $img): void { $this->collectionHandler->setAttachmentImg($this->id, $img); }

}