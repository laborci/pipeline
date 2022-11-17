<?php namespace App\Services\Attachment;

use App\Services\Attachment\Constants\TableLink;
use App\Services\Attachment\Constants\TableStorage;
use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\Event\OnDelete;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Util\Geometry\Dimension;
use Atomino2\Util\Geometry\Transform;
use Cocur\Slugify\Slugify;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Storage {


	/** @var Collection[] */
	private array $collections = [];
	public function getConnection(): Connection { return $this->connection; }
	public function getLinkTable(): string { return $this->linkTable; }
	public function getAttachmentTable(): string { return $this->attachmentTable; }

	public function __construct(EventDispatcher $eventDispatcher, private string $path, private Connection $connection, private string $storageTable, private string $linkTable, private string $attachmentTable) {
		$eventDispatcher->addListener(OnDelete::class, fn(OnDelete $event) => $this->onDelete($event));
	}

	private function onDelete(OnDelete $event) { foreach ($this->getCollections(get_class($event->getItem())) as $collection) $collection->getHandler($event->getItem())->purge(); }

	public function addCollection(Collection $collection): static {
		if (array_key_exists($collection->getUid(), $this->collections)) throw new \Exception("Collection id must be a unique");
		$this->collections[$collection->getUid()] = $collection;
		$collection->setStorage($this);
		return $this;
	}
	/**
	 * @param string $owner
	 * @return Collection[]
	 */
	public function getCollections(string $owner): array { return array_filter($this->collections, fn(Collection $item) => $item->getOwner() === $owner); }
	public function getCollectionHandler(Entity $item, string $collectionName): ?CollectionHandler {
		$owner = get_class($item);
		foreach ($this->collections as $collection) {
			if ($collection->getOwner() === $owner && $collection->getName() === $collectionName) {
				return $collection->getHandler($item);
			}
		}
		return null;
	}

	public function get(int $id): ?StoredFile {
		$row = $this->connection->getSmartQuery()->getRow(SQL::expr("SELECT * FROM :e WHERE :e=:v", $this->storageTable, TableStorage::ID, $id)->getSQL($this->connection));
		if (is_null($row)) return null;
		return new StoredFile($row, $this);
	}

	public function addFile(File $file): ?StoredFile {
		$hash = hexdec(hash_file('crc32', $file->getRealPath()));
		$size = $file->getSize();

		$fileRecord = $this->connection->getSmartQuery()->getRow(SQL::expr("SELECT * FROM :e WHERE :d('AND')", $this->storageTable, [TableStorage::HASH => $hash, TableStorage::SIZE => $size])->getSQL($this->connection));
		if (is_null($fileRecord)) {
			$slugify = new Slugify();
			$filename = $slugify->slugify(pathinfo($file->getFilename(), PATHINFO_FILENAME)) . '.' . strtolower($file->getExtension());
			$mimeType = $file->getMimeType();
			if (str_starts_with($mimeType, 'image/')) {
				$transform = 0;
				if (exif_imagetype($file) === IMAGETYPE_JPEG) {
					$exif = @exif_read_data($file->getRealPath());
					if (!empty($exif['Orientation'])) $transform = Transform::EXIF[$exif['Orientation']];
				}
				[$width, $height] = getimagesize($file->getRealPath());
				$image = json_encode([TableStorage::IMAGE_SIZE => Dimension::fromArray([Dimension::WIDTH => $width, Dimension::HEIGHT => $height]), TableStorage::IMAGE_TRANSFORM => $transform]);
			} else {
				$image = null;
			}
			$id = $this->connection->getSmartQuery()->insert($this->storageTable, [
				TableStorage::FILE        => $filename,
				TableStorage::SIZE        => $size,
				TableStorage::MIME_TYPE   => $mimeType,
				TableStorage::HASH        => $hash,
				TableStorage::IMAGE       => $image,
				TableStorage::TAGS        => null,
				TableStorage::DESCRIPTION => null,
			]);

			$path = $this->idToFullPath($id);
			$prefix = $this->idToPrefix($id);


			$filename = $prefix . '.' . $filename;
			if (!is_dir($path)) mkdir($path, 0777, true);
			if ($file instanceof UploadedFile) $file->move($path, $filename);
			else copy($file->getRealPath(), $path . '/' . $filename);
			$fileRecord = $this->connection->getSmartQuery()->getRowById($this->storageTable, $id);
		}
		return new StoredFile($fileRecord, $this);
	}

	public function setStoredFileTags(int $id, array|null $tags): void { $this->connection->getSmartQuery()->updateById($this->storageTable, $id, ['tags' => json_encode($tags)]); }
	public function setStoredFileDescription(int $id, string|null $description): void { $this->connection->getSmartQuery()->updateById($this->storageTable, $id, ['description' => $description]); }

	public function idToFullPath(int $id): string { return $this->path . '/' . $this->idToRealPath($id); }
	public function idToRealPath(int $id): string { return wordwrap(substr(str_pad($id, 9, '0', STR_PAD_LEFT), 0, -3), 3, '/', true); }
	public function idToLogicalPath(int $id): string { return wordwrap(str_pad($id, 9, '0', STR_PAD_LEFT), 3, '/', true); }
	public function idToPrefix(int $id): string { return substr(str_pad($id, 3, '0', STR_PAD_LEFT), -3); }
	public function delete(mixed $storageId): void {
		$count = $this->connection->getSmartQuery()->getValue(SQL::expr("SELECT count(:e) FROM :e WHERE :e=:v", TableLink::ID, $this->linkTable, TableLink::STORAGE_ID, $storageId)->getSQL($this->connection));
		if ($count === 0) {
			$file = $this->get($storageId);
			$this->connection->getSmartQuery()->deleteById($this->storageTable, $storageId);
			unlink($file->getRealPath());
		}
	}
}