<?php namespace App\Services\Attachment\Img;

use JetBrains\PhpStorm\ArrayShape;

class ImgResolver {

	public function __construct(private readonly ImgCreatorGD2 $creator, private readonly string $imgPath, private readonly string $secret) { }

	public function resolve(string $source, string $target, int $id, array $operations, string $password, string $file): bool {
		if (!is_dir($this->imgPath)) mkdir($this->imgPath, 0777, true);

		$passwordCheck = base_convert(crc32($id . '/' . join('/', $operations) . '/' . $this->secret . '/' . $file), 10, 36);
		if ($passwordCheck !== $password) return false;

		$op = [Img::OP_RES => 1, Img::OP_QUALITY => 80, Img::OP_FOCUS => null, Img::OP_SAFE_ZONE => null, Img::OP_TRANSFORM => 0];
		foreach ($operations as $operation) {
			$code = substr($operation, 0, 1);
			$args = substr($operation, 1);
			$op[$code] = $args;
		}

		$transform = $op[Img::OP_TRANSFORM];
		unset($op[Img::OP_TRANSFORM]);
		$res = $op[Img::OP_RES];
		unset($op[Img::OP_RES]);
		$quality = $op[Img::OP_QUALITY];
		unset($op[Img::OP_QUALITY]);
		$safeZone = $op[Img::OP_SAFE_ZONE];
		unset($op[Img::OP_SAFE_ZONE]);
		$focus = $op[Img::OP_FOCUS];
		unset($op[Img::OP_FOCUS]);

		$operation = array_keys($op)[0];
		$arguments = $op[$operation];

		$arguments = explode('.', $arguments);
		$arguments = array_map(fn(string $val) => base_convert($val, 36, 10) * $res, $arguments);
		$focus = is_null($focus) ? null : array_map(fn(string $val) => base_convert($val, 36, 10), explode('.', $focus));
		$safeZone = is_null($safeZone) ? null : array_map(fn(string $val) => base_convert($val, 36, 10), explode('.', $safeZone));

		return match ($operation) {
			Img::OP_CROP   => $this->creator->crop($arguments[0], $arguments[1], $source, $target, $transform, $quality, $safeZone, $focus),
			Img::OP_WIDTH  => $this->creator->width($arguments[0], $arguments[1], $source, $target, $transform, $quality, $safeZone, $focus),
			Img::OP_HEIGHT => $this->creator->height($arguments[0], $arguments[1], $source, $target, $transform, $quality, $safeZone, $focus),
			Img::OP_SCALE  => $this->creator->scale($arguments[0], $arguments[1], $source, $target, $transform, $quality),
			IMG::OP_BOX    => $this->creator->box($arguments[0], $arguments[1], $source, $target, $transform, $quality),
			default        => false,
		};
	}


}