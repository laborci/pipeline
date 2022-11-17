<?php namespace Atomino2\Util\Geometry;
interface Transform {

	const NORMAL_R0 = 0;
	const NORMAL_R1 = 1;
	const NORMAL_R2 = 2;
	const NORMAL_R3 = 3;
	const MIRROR_R0 = 4;
	const MIRROR_R1 = 5;
	const MIRROR_R2 = 6;
	const MIRROR_R3 = 7;

	const EXIF = [
		1 => self::NORMAL_R0,
		2 => self::MIRROR_R0,
		3 => self::NORMAL_R2,
		4 => self::MIRROR_R2,
		5 => self::MIRROR_R3,
		6 => self::NORMAL_R3,
		7 => self::MIRROR_R1,
		8 => self::NORMAL_R1,
	];

	const REVERSE = [
		self::NORMAL_R0 => self::NORMAL_R0,
		self::NORMAL_R1 => self::NORMAL_R3,
		self::NORMAL_R2 => self::NORMAL_R2,
		self::NORMAL_R3 => self::NORMAL_R1,
		self::MIRROR_R0 => self::MIRROR_R0,
		self::MIRROR_R1 => self::MIRROR_R1,
		self::MIRROR_R2 => self::MIRROR_R2,
		self::MIRROR_R3 => self::MIRROR_R3,
	];

	const OP = [
		self::NORMAL_R0 => [false, 0],
		self::NORMAL_R1 => [false, 90],
		self::NORMAL_R2 => [false, 180],
		self::NORMAL_R3 => [false, 270],
		self::MIRROR_R0 => [true, 0],
		self::MIRROR_R1 => [true, 90],
		self::MIRROR_R2 => [true, 180],
		self::MIRROR_R3 => [true, 270],
	];
}