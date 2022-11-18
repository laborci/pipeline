<?php namespace App\Services\Attachment;

use App\Services\Attachment\Constants\TableLink;
use App\Services\Attachment\Constants\TableStorage;
use App\Services\Attachment\Img\Img;
use Atomino2\Util\Geometry\Dimension;
use Atomino2\Util\Geometry\Point;
use Atomino2\Util\Geometry\Rectangle;


/**
 * @property-read string $file
 * @property-read string $mimeType;
 * @property-read int $id;
 * @property-read int $storageId;
 * @property-read int $ownerId;
 * @property-read int $collectionId;
 * @property-read int $position;
 * @property string|null $title;
 * @property-read bool $image = false;
 * @property ?Point $focus;
 * @property ?Rectangle $safeZone;
 * @property ?Rectangle $crop;
 * @property ?int $transform;
 * @property-read ?Dimension $dimensions;
 * @property-read ?int $orientationTransform;
 * @property-read string $url;
 * @property-read ?Img $img;
 */
class Attachment {

	private string      $file;
	private string      $mimeType;
	private int         $id;
	private int         $storageId;
	private int         $ownerId;
	private int         $collectionId;
	private int         $position;
	private string|null $title;
	private bool        $image = false;
	private ?Point      $focus;
	private ?Rectangle  $safeZone;
	private ?Rectangle  $crop;
	private ?int        $transform;
	private ?Dimension  $dimensions;
	private ?int        $orientationTransform;

	public function isImage(): bool { return $this->image; }

	public function __isset(string $name): bool {
		return in_array($name, ['file', 'mimeType', 'id', 'storageId', 'ownerId', 'collectionId', 'position', 'title', 'focus', 'safeZone', 'crop', 'transform', 'dimensions', 'orientationTransform', 'url', 'img']);
	}

	public function __get(string $name) {
		return match ($name) {
			'file'                 => $this->file,
			'mimeType'             => $this->mimeType,
			'id'                   => $this->id,
			'storageId'            => $this->storageId,
			'ownerId'              => $this->ownerId,
			'collectionId'         => $this->collectionId,
			'position'             => $this->position,
			'title'                => $this->title,
			'focus'                => $this->focus,
			'safeZone'             => $this->safeZone,
			'crop'                 => $this->crop,
			'transform'            => $this->transform,
			'dimensions'           => $this->dimensions,
			'orientationTransform' => $this->orientationTransform,
			'url'                  => $this->storage->url . '/' . $this->storage->idToLogicalPath($this->storageId) . '/' . $this->file,
			'img'                  => $this->isImage() ? $this->storage->imgFactory->img($this) : null,
			default                => null
		};
	}

	public function __set(string $name, $value): void {
		switch ($name) {
			case 'title':
				$this->title = $value;
				break;
			case 'focus':
				$this->focus = $value;
				break;
			case 'crop':
				$this->crop = $value;
				break;
			case 'safeZone':
				$this->safeZone = $value;
				break;
			case 'transform':
				$this->transform = $value;
				break;
		}
	}

	public function __construct(array $row, private readonly Storage $storage, private readonly ?CollectionHandler $collectionHandler = null) {
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
			$this->dimensions = Dimension::fromArray($image[TableStorage::IMAGE_SIZE]);
			$this->orientationTransform = array_key_exists(TableStorage::IMAGE_TRANSFORM, $image) ? $image[TableStorage::IMAGE_TRANSFORM] : 0;
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
	public function setPosition(int|bool $position): void { $this->collectionHandler?->setAttachmentPosition($this->id, $position); }

	public function save() {
		$img = $this->isImage() ? [
			TableLink::IMG_CROP      => $this->crop,
			TableLink::IMG_FOCUS     => $this->focus,
			TableLink::IMG_SAFE_ZONE => $this->safeZone,
			TableLink::IMG_TRANSFORM => $this->transform,
		] : null;
		$this->collectionHandler?->saveAttachmentData($this->id, $this->title, $img);
	}

}
