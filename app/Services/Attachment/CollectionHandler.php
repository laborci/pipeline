<?php namespace App\Services\Attachment;


use Atomino2\Carbonite\Entity;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\File\File;


/**
 * @property-read string[] $files
 * @property-read Collection $collection
 * @property-read int $count
 * @property-read Attachment|null $first
 * @property-read string|null $mimetype = null,
 * @property-read int $maxCount = 0,
 * @property-read int $maxSize = 0,
 */
class CollectionHandler implements \Countable, \IteratorAggregate, \ArrayAccess {

	const ID            = 'id';
	const COLLECTION_ID = 'collectionId';
	const STORAGE_ID    = 'storageId';
	const OWNER_ID      = 'ownerId';
	const POSITION      = 'position';
	const TITLE         = 'title';
	const IMG           = 'img';

	private Connection $connection;
	private string     $linkTable;
	private int        $collectionId;
	private int        $itemId;
	private string     $attachmentTable;
	/** @var Attachment[] */
	private array|null $attachments = null;

	public function __construct(private Collection $collection, private Entity $item) {
		$this->connection = $this->collection->getStorage()->getConnection();
		$this->linkTable = $this->collection->getStorage()->getLinkTable();
		$this->attachmentTable = $this->collection->getStorage()->getAttachmentTable();
		$this->collectionId = $this->collection->getUid();
		$this->itemId = $this->item->id;
	}

	public function __isset(string $name): bool { return in_array($name, ['files', 'collection', 'count', 'first', 'mimeType', 'maxSize', 'maxCount']); }
	public function __get(string $name) {
		return match ($name) {
			'files'      => array_map(fn(Attachment $attachment) => $attachment->file, $this->getAttachments()),
			'collection' => $this->collection,
			'count'      => $this->count(),
			'first'      => $this->get(),
			'mimeType'   => $this->collection->getMimeType(),
			'maxSize'    => $this->collection->getMaxFileSize(),
			'maxCount'   => $this->collection->getMaxFileCount(),
			default      => null
		};
	}

	/** @return Attachment[] */
	private function getAttachments(bool $force = false): array {
		if (is_null($this->attachments) || $force) {
			$this->attachments = [];
			$rows = $this->connection->getSmartQuery()->getRows(SQL::expr(
				"SELECT * FROM :e WHERE :d('AND') ORDER BY :e",
				$this->attachmentTable,
				[self::COLLECTION_ID => $this->collectionId, self::OWNER_ID => $this->itemId],
				self::POSITION
			)->getSQL($this->connection));
			foreach ($rows as $row) {
				$attachment = new Attachment($this, $row);
				$this->attachments[] = $attachment;
			}
		}
		return $this->attachments;
	}

	/**
	 * Links an already stored file to the collection.
	 *
	 * @param int|StoredFile $file
	 * @return void
	 * @throws AttachmentException
	 */
	public function link(int|StoredFile $file) {
		if (is_int($file)) $file = $this->collection->getStorage()->get($file);
		if (is_null($file)) throw new AttachmentException("File to be linked is not exists");
		$this->validateFile($file);
		$this->connection->getSmartQuery()->insert($this->linkTable, [
			self::COLLECTION_ID => $this->collectionId,
			self::STORAGE_ID    => $file->getId(),
			self::OWNER_ID      => $this->itemId,
			self::POSITION      => $this->count(),
		], true);
	}

	/**
	 * Store a file, then links it to the collection
	 *
	 * @param File $file
	 * @return void
	 * @throws AttachmentException
	 */
	public function addFile(File $file): void {
		if ($this->validateFile($file)) {
			$storedFile = $this->collection->getStorage()->addFile($file);
			$this->link($storedFile);
		}
	}

	private function validateFile(File $file): bool {
		if (!is_null($this->collection->getMaxFileCount()) && $this->collection->getMaxFileCount() <= $this->count) throw new AttachmentException(sprintf("Max file count exceeded. (%d)", $this->collection->getMaxFileCount()));
		if (!is_null($this->collection->getMaxFileSize()) && $this->collection->getMaxFileSize() < $file->getSize()) throw new AttachmentException(sprintf("File too big. (%d)", $this->collection->getMaxFileSize()));
		if (!is_null($this->collection->getMimeType()) && !array_reduce($this->collection->getMimeType(), fn(bool $res, string $pattern) => $res || fnmatch($pattern, $file->getMimeType()), false)) throw new AttachmentException(sprintf("File type (%s) not allowed. (%s)", $file->getMimeType(), join(', ', $this->collection->getMimeType())));
		return true;
	}

	/**
	 * Removes a linked file from the collection
	 * @param int $id
	 * @return void
	 */
	public function remove(int $id): void {
		$attachment = $this->get($id);
		if (is_null($attachment)) return;
		$this->connection->getSmartQuery()->deleteById($this->linkTable, $id);
		$this->reorder();
		$this->collection->getStorage()->delete($attachment->storageId);
	}

	/**
	 * Removes all linked files from the collection
	 * @return void
	 */
	public function purge(): void {
		foreach ($this->getAttachments(true) as $attachment) {
			$this->connection->getSmartQuery()->deleteById($this->linkTable, $attachment->id);
			$this->collection->getStorage()->delete($attachment->storageId);
		}
	}

	private function reorder() {
		$map = [];
		if ($this->count === 0) return;
		foreach ($this->getAttachments() as $position => $attachment) $map[$attachment->id] = $position;
		$this->connection->query(
			SQL::expr("UPDATE :e SET :e = CASE :e :d('', 'WHEN :v THEN :v') END WHERE :r",
				$this->linkTable,
				self::POSITION,
				self::ID,
				$map,
				SQL::cmp('id', ...array_keys($map))
			)->getSQL($this->connection)
		);
	}

	public function setAttachmentPosition(int $id, int|bool $position = true) {
		if (!is_null($attachment = $this->get($id))) {
			if ($position === true) $position = $this->count;
			$currentPosition = array_search($attachment, $this->attachments);
			if ($currentPosition === $position) return;
			array_splice($this->attachments, $currentPosition, 1);
			array_splice($this->attachments, $position, 0, [$attachment]);
			$this->reorder();
		}
	}
	public function setAttachmentTitle(int $id, null|string $title) {
		if (!is_null($this->get($id))) {
			$this->connection->getSmartQuery()->updateById($this->linkTable, $id, [self::TITLE => $title]);
			$this->getAttachments(true);
		}
	}
	public function setAttachmentImg(int $id, null|array $img) {
		if (!is_null($this->get($id))) {
			$this->connection->getSmartQuery()->updateById($this->linkTable, $id, [self::IMG => json_encode($img)]);
			$this->getAttachments(true);
		}
	}


	public function find(string $pattern): array { return array_filter($this->getAttachments(), fn(Attachment $attachment) => fnmatch($pattern, $attachment->file)); }
	public function filter(string $mimeType): array { return array_filter($this->getAttachments(), fn(Attachment $attachment) => fnmatch($mimeType, $attachment->mimeType)); }
	public function get(string|int|null $filename = null): Attachment|null {
		if (0 === count($this->getAttachments())) return null;
		if (is_null($filename)) return $this->getAttachments()[0];
		foreach ($this->getAttachments() as $attachment) {
			if (is_int($filename)) {
				if ($attachment->id === $filename) return $attachment;
			} elseif (str_starts_with($attachment->file, $filename)) return $attachment;
		}
		return null;
	}

	#region Countable
	public function count(): int { return count($this->getAttachments()); }
	#endregion
	#region IteratorAggregate
	public function getIterator(): CollectionHandlerIterator { return new CollectionHandlerIterator(fn() => $this->getAttachments()); }
	#endregion
	#region ArrayAccess
	/**
	 * @param mixed $offset
	 * @return Attachment|null
	 */
	public function offsetGet(mixed $offset): mixed {
		if (is_numeric($offset)) return $this->getAttachments()[$offset];
		return $this->get($offset);
	}

	public function offsetExists(mixed $offset): bool {
		if (is_numeric($offset)) return array_key_exists($offset, $this->getAttachments());
		return in_array($offset, $this->getAttachments());
	}

	#[Deprecated('OUT OF ORDER')] public function offsetSet(mixed $offset, mixed $value): void { }
	#[Deprecated('OUT OF ORDER')] public function offsetUnset(mixed $offset): void { }
	#endregion
}

