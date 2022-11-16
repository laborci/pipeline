<?php namespace App\Bundle\Attachment;

class Collection {
	private ?string $mimetype     = null;
	private ?int    $maxFileSize  = null;
	private ?int    $maxFileCount = null;
	private string  $name;
	private Storage $storage;
	/** @var Attachment[] */
	private array   $attachments;

	public function getName(): string { return $this->name; }

	public function __construct(Storage $storage, string $name, ?int $maxFileCount = null, ?int $maxFileSize = null, string|null $mimetype = null) {
		$this->storage = $storage;
		$this->name = $name;
		$this->maxFileCount = $maxFileCount;
		$this->maxFileSize = $maxFileSize;
		$this->mimetype = $mimetype;
	}

	public function addFile($fileName) {
		if ($this->storage->hasFile($fileName) && $this->validateFile($fileName)) $this->attachments[] = new Attachment($this->storage->getFile($fileName), $this);
	}

	public function hasFile(int|string $fileName): bool {
		foreach ($this->attachments as $attachment){
			if($attachment->file === $fileName) return true;
		}
		return false;
	}

	private function validateFile($fileName): bool {
		return true;
		//TODO: make validation
	}
	public function getAttachments() {
		return $this->attachments;
	}

}

/*
 * {
	"files": {
		"avatar.jpg": {
			"size": 658533,
			"mimetype": "image/jpeg",
			"title": "avatar.jpg",
			"image":{
				"width": 1152,
				"height": 1445,
				"quality": null,
				"focus": "0cc0a1",
				"safezone": "00a00a0tl0o0",
			}
		}
	},
	"collections": {
		"avatar": [
			"avatar.jpg"
		],
		"images": [],
		"gallery": [],
		"downloads": []
	}
}
 */