<?php namespace App\Services\Attachment;

use App\Services\Attachment\Constants\TableLink;
use App\Services\Attachment\Constants\TableStorage;
use Atomino2\Util\Geometry\Dimension;
use Atomino2\Util\Geometry\Point;
use Atomino2\Util\Geometry\Rectangle;

class Attachment {

	public readonly string      $file;
	public readonly string      $mimeType;
	public readonly int         $id;
	public readonly int         $storageId;
	public readonly int         $ownerId;
	public readonly int         $collectionId;
	public readonly int         $position;
	public readonly string|null $title;

	private bool                $image = false;
	private ?Point     $focus;
	private ?Rectangle $safeZone;
	private ?Rectangle $crop;
	private ?int       $transform;
	private ?Dimension $dimensions;
	private ?int       $orientationTransform;

	public function isImage(): bool { return $this->image; }
	public function getFocus(): ?Point { return $this->focus; }
	public function getSafeZone(): ?Rectangle { return $this->safeZone; }
	public function getCrop(): ?Rectangle { return $this->crop; }
	public function getOrientationTransform(): ?int { return $this->orientationTransform; }
	public function getTransform(): ?int { return $this->transform; }
	public function getDimensions(): ?Dimension { return $this->dimensions; }

	public function __construct(private readonly CollectionHandler $collectionHandler, array $row) {
		$this->id = $row[TableLink::ID];
		$this->file = $row[TableStorage::FILE];
		$this->collectionId = $row[TableLink::COLLECTION_ID];
		$this->mimeType = $row[TableStorage::MIME_TYPE];
		$this->ownerId = $row[TableLink::OWNER_ID];
		$this->position = $row[TableLink::POSITION];
		$this->storageId = $row[TableLink::STORAGE_ID];
		$this->title = $row[TableLink::TITLE];
		if (!is_null($row[TableStorage::IMAGE])) {
			$this->image = true;
			$image = json_decode($row[TableStorage::IMAGE], true);
			$this->dimensions = Dimension::fromArray($image);
			$this->orientationTransform = $image[TableLink::IMG_TRANSFORM];
			$img = is_null($row[TableLink::IMG]) ? null : json_decode($row[TableLink::IMG], true);
			if (!is_null($img)) {
				$this->focus = is_null($img[TableLink::IMG_FOCUS]) ? null : Point::fromArray($img[TableLink::IMG_FOCUS]);
				$this->safeZone = is_null($img[TableLink::IMG_SAFE_ZONE]) ? null : Rectangle::fromArray($img[TableLink::IMG_SAFE_ZONE]);
				$this->crop = is_null($img[TableLink::IMG_CROP]) ? null : Rectangle::fromArray($img[TableLink::IMG_CROP]);
				$this->transform = is_null($img[TableLink::IMG_TRANSFORM]) ? 0 : $img[TableLink::IMG_TRANSFORM];
			}
		}
	}

	/**
	 * Sets the position of the link in the collection
	 *
	 * @param int|bool $position
	 * @return void
	 */
	public function setPosition(int|bool $position): void { $this->collectionHandler->setAttachmentPosition($this->id, $position); }


	/**
	 * Sets the title of the file
	 *
	 * @param string|null $title
	 * @return void
	 */
	public function setTitle(string|null $title): void { $this->collectionHandler->setAttachmentTitle($this->id, $title); }

	/**
	 * Changes the image creation method
	 * Pass FALSE to any value you don't want to modify
	 *
	 * @param false|Point $focus
	 * @param false|Rectangle $safeZone
	 * @param false|Rectangle|null $crop
	 * @param false|int $transform
	 * @return void
	 */
	public function setImg(
		false|Point          $focus = false,
		false|Rectangle      $safeZone = false,
		false|null|Rectangle $crop = false,
		false|int            $transform = false
	): void {
		$this->collectionHandler->setAttachmentImg($this->id, [
			TableLink::IMG_TRANSFORM => $transform === false ? $this->getTransform() : $transform,
			TableLink::IMG_FOCUS     => $focus === false ? $this->getFocus() : $focus,
			TableLink::IMG_CROP      => $crop === false ? $this->getCrop() : $crop,
			TableLink::IMG_SAFE_ZONE => $safeZone === false ? $this->getSafeZone() : $safeZone,
		]);
	}

}
