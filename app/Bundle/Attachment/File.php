<?php namespace App\Bundle\Attachment;

/**
 * @property-read $name
 */
class File {
	private string $name;

	public function __construct(string $name, $data) {
		$this->name = $name;
	}

	public function __get(string $name):mixed{
		return match ($name){
			'name'=>$this->name
		};
	}
}