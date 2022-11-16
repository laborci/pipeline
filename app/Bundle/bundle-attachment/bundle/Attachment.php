<?php namespace Atomino\Bundle\Attachment;

use Atomino\Bundle\Attachment\Img\Img;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @property-read string $url
 * @property-read string $path
 * @property-read string $mimetype
 * @property-read string $filename
 * @property-read string $title
 * @property-read int|null $width
 * @property-read int|null $height
 * @property-read string|null $safezone
 * @property-read string|null $focus
 * @property-read int|null $quality
 * @property-read int $size
 * @property-read File $file
 * @property-read \Atomino\Bundle\Attachment\Storage $storage
 * @property-read bool $isImage
 * @property-read \Atomino\Bundle\Attachment\Img\Img|null $image
 */
class Attachment implements \JsonSerializable {

	private AttachmentConfig $config;

	public function __construct(
		private Storage     $storage,
		private string      $filename,
		private int         $size,
		private string      $mimetype,
		private string      $title = '',
		private array       $properties = [],
		private int|null    $width = null,
		private int|null    $height = null,
		private string|null $safezone = null,
		private string|null $focus = null,
		private int|null    $quality = null
	) {
		$this->config = $this->storage->getContainer()->get(AttachmentConfig::class);
	}

	#[Pure] public function __isset(string $name): bool {
		return in_array($name, [
			'mimetype',
			'filename',
			'title',
			'size',
			'url',
			'path',
			'storage',
			'isImage',
			'file',
			'image',
			'width',
			'height',
			'safezone',
			'focus',
			'quality',
		]);
	}

	public function __get(string $name) {
		return match ($name) {
			'mimetype' => $this->mimetype,
			'filename' => $this->filename,
			'title' => $this->title,
			'size' => $this->size,
			'url' => $this->storage->url . $this->filename,
			'path' => $this->storage->path . $this->filename,
			'storage' => $this->storage,
			'isImage' => str_starts_with($this->mimetype, 'image/'),
			'file' => new File($this->path),
			'image' => $this->isImage ? new Img($this, $this->storage->getContainer()) : null,
			'width' => $this->width,
			'height' => $this->height,
			'focus' => $this->focus,
			'safezone' => $this->safezone,
			'quality' => $this->quality,
			default => null
		};
	}

	public function setWidth(int|null $width) {
		$this->width = $width;
		$this->storage->persist();
	}
	public function setHeight(int|null $height) {
		$this->height = $height;
		$this->storage->persist();
	}
	public function setSafezone(string|null $safezone) {
		$this->safezone = $safezone;
		$this->storage->persist();
	}
	public function setFocus(string|null $focus) {
		$this->focus = $focus;
		$this->storage->persist();
	}
	public function setTitle(string|null $title) {
		$this->title = $title;
		$this->storage->persist();
	}
	public function setQuality(int|null $quality) {
		$this->quality = $quality;
		$this->storage->persist();
	}

	public function delete() {
		$this->deleteImages();
		$this->storage->delete($this->filename);
	}

	public function deleteImages() {
		$files = glob($this->config["img.path"] . '/*.*.' . str_replace('/', '', $this->storage->subPath) . '.*.*');
		foreach ($files as $file) unlink($file);
	}

	public function rename($newName) { $this->storage->rename($this->filename, $newName); }

	public function restrictAccess() {
		touch($this->path . $this->config["restricted-access-postfix"]);
	}

	public function allowAccess() {
		file_exists($this->path . $this->config["restricted-access-postfix"]) && touch($this->path . $this->config["restricted-access-postfix"]);
	}

	#region property get / set
	public function getProperties(): array { return $this->properties; }
	#[Pure] public function getProperty(string $name): string|null { return array_key_exists($name, $this->properties) ? $this->properties[$name] : null; }
	public function setProperty(string $name, string|null $value) {
		if (!is_null($value)) $this->properties[$name] = $value;
		elseif (array_key_exists($name, $this->properties)) unset($this->properties[$name]);
		$this->storage->persist();
	}
	public function setProperties($data) { $this->properties = $data; }

	#endregion

	public function jsonSerialize(): mixed {
		return [
			'size'       => $this->size,
			'mimetype'   => $this->mimetype,
			'title'      => $this->title,
			'properties' => $this->properties,
			'width'      => $this->width,
			'height'     => $this->height,
			'safezone'   => $this->safezone,
			'focus'      => $this->focus,
			'quality'    => $this->quality,
		];
	}
}