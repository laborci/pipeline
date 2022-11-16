<?php namespace App\Bundle\Attachment;
class StorageFactory {
	private string $path;
	private string $imgUrl;
	private string $fileUrl;
	public function __construct(string $path, string $fileUrl, string $imgUrl) {
		$this->fileUrl = $fileUrl;
		$this->imgUrl = $imgUrl;
		$this->path = $path;
	}
	public function createStorage(): Storage {
		return new Storage($this->path, $this->fileUrl, $this->imgUrl);
	}
}