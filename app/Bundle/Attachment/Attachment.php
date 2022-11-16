<?php namespace App\Bundle\Attachment;

class Attachment {

	public function __construct(private File $file, private Collection $collection) {

	}

	public function __get(string $name): mixed {
		return match ($name) {
			'name' => $this->file->name
		};
	}
}