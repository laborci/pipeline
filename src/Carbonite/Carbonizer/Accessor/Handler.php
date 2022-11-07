<?php namespace Atomino2\Carbonite\Carbonizer\Accessor;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Accessor;

class Handler extends Accessor {

	private string $method;
	public function getMethod(): string { return $this->method; }
	public function getAccess(): int { return Access::READ; }
	public function __construct(string $method, string $type) {
		$this->method = $method;
		parent::__construct($type);
	}
}