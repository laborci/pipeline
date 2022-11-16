<?php namespace App\Services\Attachment;

use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\Event\OnDelete;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
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
		$row = $this->connection->getSmartQuery()->getRow(SQL::expr("SELECT * FROM :e WHERE :e=:v", $this->storageTable, 'id', $id)->getSQL($this->connection));
		if(is_null($row)) return null;
		return new StoredFile($row, $this);
	}
	public function addFile(File $file): ?StoredFile {
		$hash = hexdec(hash_file('crc32', $file->getRealPath()));
		$size = $file->getSize();

		$fileRecord = $this->connection->getSmartQuery()->getRow(SQL::expr("SELECT * FROM :e WHERE :e=:v AND size=:v", $this->storageTable, 'hash', $hash, $size)->getSQL($this->connection));
		if (is_null($fileRecord)) {
			$slugify = new Slugify();
			$filename = $slugify->slugify(pathinfo($file->getFilename(), PATHINFO_FILENAME)) . '.' . strtolower($file->getExtension());
			$mimeType = $file->getMimeType();
			if (str_starts_with($mimeType, 'image/')) {
				[$width, $height] = getimagesize($file->getRealPath());
				$imageDimensions = json_encode(["width" => $width, "height" => $height]);
			} else {
				$imageDimensions = null;
			}
			$id = $this->connection->getSmartQuery()->insert($this->storageTable, [
				'file'            => $filename,
				'size'            => $size,
				'mimeType'        => $mimeType,
				'hash'            => $hash,
				'imageDimensions' => $imageDimensions,
				'tags'            => null,
				'description'     => null,
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
		$count = $this->connection->getSmartQuery()->getValue(SQL::expr("SELECT count(:e) FROM :e WHERE :e=:v", 'id', $this->linkTable, 'storageId', $storageId)->getSQL($this->connection));
		if ($count === 0) {
			$file = $this->get($storageId);
			$this->connection->getSmartQuery()->deleteById($this->storageTable, $storageId);
			unlink($file->getRealPath());
		}
	}
}



//$handler = new CollectionHandler();
//
//$handler->addFile(File);
//
///** Attachment */
//$handler->first;
//$handler[1];
//
//$handler->first->moveToPosition(4);