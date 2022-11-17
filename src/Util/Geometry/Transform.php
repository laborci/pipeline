<?php namespace Atomino2\Util\Geometry;
interface Transform {
	const FLIP_VERTICALLY   = 1 << 0;
	const FLIP_HORIZONTALLY = 1 << 1;
	const ROTATE_90         = 1 << 2;
	const ROTATE_180        = 1 << 3;
	const ROTATE_270        = 1 << 4;

	const EXIF = [
		1 => 0,
		2 => self::FLIP_HORIZONTALLY,
		3 => self::ROTATE_180,
		4 => self::FLIP_VERTICALLY,
		5 => self::ROTATE_90 + self::FLIP_HORIZONTALLY,
		6 => self::ROTATE_270,
		7 => self::ROTATE_90 + self::FLIP_VERTICALLY,
		8 => self::ROTATE_90,
	];

}