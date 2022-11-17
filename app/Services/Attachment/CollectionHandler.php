<?php namespace App\Services\Attachment;


use App\Services\Attachment\Constants\TableLink;
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
 * @property-read string|null $mimetype
 * @property-read int $maxCount
 * @property-read int $maxSize
 */
class CollectionHandler implements \Countable, \IteratorAggregate, \ArrayAccess {

	/** @var Attachment[] */
	private array|null          $attachments = null;
	private readonly Connection $connection;
	private readonly string     $linkTable;
	private readonly int        $collectionId;
	private readonly int        $itemId;
	private readonly string     $attachmentTable;
	private readonly Collection $collection;
	private readonly Entity     $item;

	public function __construct(Collection $collection, Entity $item) {
		$this->item = $item;
		$this->collection = $collection;
		$this->connection = $this->collection->storage->connection;
		$this->linkTable = $this->collection->storage->linkTable;
		$this->attachmentTable = $this->collection->storage->attachmentTable;
		$this->collectionId = $this->collection->uid;
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
				[TableLink::COLLECTION_ID => $this->collectionId, TableLink::OWNER_ID => $this->itemId],
				TableLink::POSITION
			)->getSQL($this->connection));
			foreach ($rows as $row) {
				$attachment = new Attachment($row, $this->collection->storage, $this);
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
		if (is_int($file)) $file = $this->collection->storage->get($file);
		if (is_null($file)) throw new AttachmentException("File to be linked is not exists");
		$this->validateFile($file);
		$img = $file->isImage() ? json_encode([
			TableLink::IMG_TRANSFORM => 0,
			TableLink::IMG_CROP      => null,
			TableLink::IMG_FOCUS     => null,
			TableLink::IMG_SAFE_ZONE => null,
		]) : null;
		$this->connection->getSmartQuery()->insert(
			$this->linkTable,
			[
				TableLink::COLLECTION_ID => $this->collectionId,
				TableLink::STORAGE_ID    => $file->id,
				TableLink::OWNER_ID      => $this->itemId,
				TableLink::POSITION      => $this->count(),
				TableLink::IMG           => $img,
			],
			true);
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
			$storedFile = $this->collection->storage->addFile($file);
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
		$this->collection->storage->delete($attachment->storageId);
	}

	/**
	 * Removes all linked files from the collection
	 * @return void
	 */
	public function purge(): void {
		foreach ($this->getAttachments(true) as $attachment) {
			$this->connection->getSmartQuery()->deleteById($this->linkTable, $attachment->id);
			$this->collection->storage->delete($attachment->storageId);
		}
	}

	private function reorder() {
		$map = [];
		if ($this->count === 0) return;
		foreach ($this->getAttachments() as $position => $attachment) $map[$attachment->id] = $position;
		$this->connection->query(
			SQL::expr("UPDATE :e SET :e = CASE :e :d('', 'WHEN :v THEN :v') END WHERE :r",
				$this->linkTable,
				TableLink::POSITION,
				TableLink::ID,
				$map,
				SQL::cmp('id', ...array_keys($map))
			)->getSQL($this->connection)
		);
	}

	/**
	 * Sets the position of a link
	 *
	 * @param int $id
	 * @param int|null $position null means the last position in the collection
	 * @return void
	 */
	public function setAttachmentPosition(int $id, null|int $position = null) {
		if (!is_null($attachment = $this->get($id))) {
			if ($position === null) $position = $this->count;
			$currentPosition = array_search($attachment, $this->attachments);
			if ($currentPosition === $position) return;
			array_splice($this->attachments, $currentPosition, 1);
			array_splice($this->attachments, $position, 0, [$attachment]);
			$this->reorder();
		}
	}
	/**
	 * Persist changes of linked file
	 * Do not call this method directly, only through Attachment::save method
	 *
	 * @param int $id
	 * @param string $title
	 * @param array|null $img
	 * @return void
	 */
	public function saveAttachmentData(int $id, string $title, null|array $img) {
		if (!is_null($this->get($id))) $this->connection->getSmartQuery()->updateById($this->linkTable, $id, [TableLink::TITLE => $title, TableLink::IMG => $img]);
	}

	/**
	 * Searches for files by name with fnmatch.
	 *
	 * @param string $pattern
	 * @return array
	 */
	public function find(string $pattern): array { return array_filter($this->getAttachments(), fn(Attachment $attachment) => fnmatch($pattern, $attachment->file)); }

	/**
	 * Searches for files by mimetype fnmach
	 *
	 * @param string $mimeType
	 * @return array
	 */
	public function filter(string $mimeType): array { return array_filter($this->getAttachments(), fn(Attachment $attachment) => fnmatch($mimeType, $attachment->mimeType)); }

	/**
	 * Returns one file
	 * when filename is null it will return the first linked file
	 * when filename is int, it will search by linkid
	 * when filename is string it will search by filename starts with
	 *
	 * @param string|int|null $filename
	 * @return Attachment|null
	 */
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

