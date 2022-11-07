<?php namespace Atomino2\Carbonite\Carbonizer\Accessor;

use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Accessor;

class GetSet extends Accessor {

	private string|bool $getMethod;
	private string|bool $setMethod;
	private int         $access = Access::HIDDEN;

	public function getAccess(): int { return $this->access; }
	public function getGetMethod(): bool|string { return $this->getMethod; }
	public function getSetMethod(): bool|string { return $this->setMethod; }

	public function __construct(string|bool $getMethod, string|bool $setMethod, string $type) {
		$this->setMethod = $setMethod;
		$this->getMethod = $getMethod;
		if ($getMethod !== false) $this->access += Access::READ;
		if ($setMethod !== false) $this->access += Access::WRITE;
		parent::__construct($type);
	}
}