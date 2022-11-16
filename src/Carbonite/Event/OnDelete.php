<?php namespace Atomino2\Carbonite\Event;

use Atomino2\Carbonite\Entity;

class OnDelete extends EventInterface {
	public function getId(): int { return $this->id; }
	public function __construct(Entity $item, private readonly int $id) {
		parent::__construct($item);
	}
}