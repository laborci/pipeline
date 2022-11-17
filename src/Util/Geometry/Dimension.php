<?php namespace Atomino2\Util\Geometry;
class Dimension implements \JsonSerializable {
	const WIDTH = 'width';
	const HEIGHT = 'height';
	public function __construct(public int $width, public int $height) { }
	static function fromArray(array $array): static { return new static($array['width'], $array['height']); }
	public function jsonSerialize(): mixed { return ["width" => $this->width, "height" => $this->height]; }
}