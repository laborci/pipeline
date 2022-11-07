<?php namespace Atomino2\Carbonite\Event;

class OnDelete extends EventInterface {
	public function getId(): int { return $this->id; }
	public function __construct(private readonly int $id) { }
}