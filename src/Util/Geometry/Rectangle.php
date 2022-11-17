<?php namespace Atomino2\Util\Geometry;

use Atomino2\Util\Geometry\Point;

/**
 * @property-read int $x
 * @property-read int $y
 * @property-read int $width
 * @property-read int $height
 */
class Rectangle implements \JsonSerializable {

	public function __isset(string $name): bool {
		return in_array($name, ['x', 'y', 'width', 'height']);
	}

	public function __get(string $name) {
		return match ($name) {
			'x'      => $this->position->x,
			'y'      => $this->position->y,
			'width'  => $this->size->width,
			'height' => $this->size->height,
			default  => null
		};
	}


	public function __construct(public Point $position, public Dimension $size) { }
	static function createWithTwoPoints(Point $p1, Point $p2): static {
		$x = min($p1->x, $p2->x);
		$y = min($p1->y, $p2->y);
		$width = abs($p1->x - $p2->x);
		$height = abs($p1->y - $p2->y);
		return new static(new Point($x, $y), new Dimension($width, $height));
	}
	static function fromArray(array $array): static { return new static(Point::fromArray($array['position']), Dimension::fromArray($array['size'])); }

	public function containsPoint(Point $point): bool {
		return
			$point->x >= $this->position->x &&
			$point->x <= $this->position->x + $this->size->width &&
			$point->y >= $this->position->y &&
			$point->y <= $this->position->y + $this->size->height;
	}
	public function jsonSerialize(): mixed { return ["position" => $this->position, 'size' => $this->size]; }
}