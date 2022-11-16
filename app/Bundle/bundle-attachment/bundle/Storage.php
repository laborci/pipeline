<?php namespace Atomino\Bundle\Attachment;

use Atomino\Bundle\Attachment\Img\ImgCreatorInterface;
use Atomino\Carbon\Entity;
use DI\Container;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use VRia\Utils\NoDiacritic;
use function Atomino\debug;

/**
 * @property-read string $path
 * @property-read string $url
 * @property-read string $subPath
 * @property-read \Atomino\Bundle\Attachment\Attachment[] $attachments
 * @property-read \Atomino\Bundle\Attachment\Collection[] $collections
 */
class Storage implements \JsonSerializable {

	/** @var \Atomino\Bundle\Attachment\Collection[] */
	private array $collections = [];
	private string $path = '';
	private string $url;
	/** @var \Atomino\Bundle\Attachment\Attachment[] */
	private array $attachments = [];
	private int $transaction = 0;
	private array $collectionStorages = [];
	private string $subPath;

	/**
	 * Storage constructor.
	 * @param \Atomino\Carbon\Entity $entity
	 * @param array $attachments
	 * @param \Atomino\Bundle\Attachment\AttachmentCollectionInterface[] $collections
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function __construct(private Entity $entity, array $attachments, array $collections, private string $field, private Container $container) {
		$config = $this->container->get(AttachmentConfig::class);
		if (is_null($entity->id)) return;

		$itemPath = (function ($id) {
			$id36 = str_pad(base_convert($id, 10, 36), 6, '0', STR_PAD_LEFT);
			return substr($id36, 0, 2) .
				'/' . substr($id36, 2, 2) .
				'/' . substr($id36, 4, 2) . '/';
		})($entity->id);
		$this->subPath = $this->entity::model()->getTable() . '/' . $itemPath;
		$this->path = $config("path") . '/' . $this->subPath;
		$this->url = $config('url') . '/' . $this->subPath;

		// Create Collections
		foreach ($collections as $collection) {
			$this->collectionStorages[$collection->field] = array_key_exists($collection->field, $attachments['collections']) ? $attachments['collections'][$collection->field] : [];
			$this->collections[$collection->field] = new Collection(
				$collection,
				$this,
				$this->collectionStorages[$collection->field]
			);
		}
		// Fill files
		foreach ($attachments['files'] as $filename => $file) {
			$this->attachments[$filename] = new Attachment(
				$this,
				$filename,
				$file['size'],
				$file['mimetype'],
				$file['title'],
				$file['properties'],
				$file['width'],
				$file['height'],
				$file['safezone'],
				$file['focus'],
				$file['quality'],
			);
		}
	}

	public function getContainer(): Container { return $this->container; }

	#[Pure] public function __isset(string $name): bool { return in_array($name, ['path', 'url', 'collections', 'attachments', 'subPath']); }
	public function __get(string $name) {
		return match ($name) {
			'path' => $this->path,
			'url' => $this->url,
			'collections' => $this->collections,
			'attachments' => $this->attachments,
			'subPath' => $this->subPath,
			default => null
		};
	}

	#[Pure] public function getCollection(string $name): Collection|null { return array_key_exists($name, $this->collections) ? $this->collections[$name] : null; }
	public function addFile(File $file): string {
		if (!is_dir($this->path)) mkdir($this->path, 0777, true);
		if ($file instanceof UploadedFile) {
			$filename = static::normalizeFileName($file->getClientOriginalName());
			$file = $file->move($this->path, $filename);
		} else {
			$filename = static::normalizeFileName($file->getFilename());
			copy($file->getRealPath(), $this->path . $filename);
			$file = new File($this->path . $filename);
		}
		$attachment = new Attachment(
			$this,
			$file->getFilename(),
			$file->getSize(),
			$file->getMimeType(),
			'',
			[]);
		if ($attachment->isImage) {
			$size = $this->container->get(ImgCreatorInterface::class)->getDimensions($attachment->path);
			$attachment->setWidth($size['width']);
			$attachment->setHeight($size['height']);
		}
		$this->attachments[$filename] = $attachment;
		$this->persist();
		return $filename;
	}
	public function delete(string $filename) {
		if ($this->has($filename)) {
			$this->begin();
			foreach ($this->collections as $collection) $collection->remove($filename);
			unset($this->attachments[$filename]);
			if(file_exists($this->path . $filename))unlink($this->path . $filename);
			$this->commit();
		}
	}
	public function purge() {
		$this->begin();
		foreach ($this->attachments as $attachment) $attachment->delete();
		rmdir($this->path);
		$this->commit();
	}
	public function rename(string $filename, string $newName): void {
		$pathInfo = pathinfo($filename);
		$newName = self::normalizeFileName($newName);
		if (!str_ends_with($newName, '.' . $pathInfo["extension"])) $newName .= '.' . $pathInfo["extension"];
		if ($filename === $newName) return;
		$this->begin();
		$oldAttachment = $this->getAttachment($filename);
		$oldAttachment->deleteImages();
		if (file_exists($this->path . $newName)) $this->delete($newName);
		rename($this->path . $filename, $this->path . $newName);
		unset($this->attachments[$filename]);
		$file = new File($this->path . $newName);
		$this->attachments[$newName] = new Attachment($this,
			$file->getFilename(),
			$oldAttachment->size,
			$oldAttachment->mimetype,
			$oldAttachment->title,
			$oldAttachment->getProperties(),
			$oldAttachment->width,
			$oldAttachment->height,
			$oldAttachment->safezone,
			$oldAttachment->focus,
			$oldAttachment->quality,
		);
		foreach ($this->collectionStorages as &$collectionStorage) {
			if (($index = array_search($filename, $collectionStorage)) !== false) {
				$collectionStorage[$index] = $newName;
			}
		}
		$this->commit();
	}
	#[Pure] public function has(string $filename): bool { return array_key_exists($filename, $this->attachments); }
	#[Pure] public function getAttachment(string $filename): null|Attachment { return $this->has($filename) ? $this->attachments[$filename] : null; }

	#region persist
	public function begin() {
		$this->transaction++;
	}
	public function commit() {
		if ($this->transaction > 0) {
			$this->transaction--;
			$this->persist();
		}
	}
	public function persist() {
		if (is_null($this->entity->id) || $this->transaction !== 0) return;
		$this->entity::model()->getConnection()->getSmart()->updateById($this->entity::model()->getTable(), $this->entity->id, [$this->field => json_encode($this)]);
	}
	#endregion

	public static function normalizeFileName(string $filename): string {
		$filename = mb_strtolower($filename);
		$pathInfo = pathinfo($filename);
		$filename = $pathInfo['filename'];
		$filename = NoDiacritic::filter($filename);
		$filename = preg_replace('/[^a-zA-Z0-9\.]/', '-', $filename);
		$filename = preg_replace('/-+/', '-', $filename);
		$filename = preg_replace('/\.+/', '.', $filename);
		$filename = trim($filename, '.-');
		return $filename . (array_key_exists('extension', $pathInfo) ? '.' . $pathInfo['extension'] : '');
	}
	public function jsonSerialize():mixed {
		return [
			'files'       => $this->attachments,
			'collections' => $this->collectionStorages,
		];
	}
}