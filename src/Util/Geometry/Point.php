<?php namespace Atomino2\Util\Geometry;
class Point implements \JsonSerializable {
	public function __construct(public int $x, public int $y) { }
	static function fromArray(array $array): static { return new static($array['x'], $array['y']); }
	public function jsonSerialize(): mixed { return ["x" => $this->x, "y" => $this->y]; }
}