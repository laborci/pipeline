<?php namespace Atomino2\Carbonite\Carbonizer;

abstract class Accessor {

	private string $type;
	public function getType(): string { return $this->type; }

	abstract public function getAccess():int;

	public function __construct(string $type) {
		$this->type = $type;
	}

}