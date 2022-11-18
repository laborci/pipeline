<?php namespace Atomino2\Attachment\Img;

use Atomino2\Attachment\Attachment;

/**
 * @property string $png
 * @property string $jpg
 * @property string $webp
 * @property string $gif
 * @property string $url
 */
class Img {

	const OP_SCALE     = 's';
	const OP_CROP      = 'c';
	const OP_BOX       = 'b';
	const OP_QUALITY   = 'q';
	const OP_WIDTH     = 'w';
	const OP_HEIGHT    = 'h';
	const OP_FOCUS     = 'f';
	const OP_SAFE_ZONE = 'z';
	const OP_RES       = 'x';
	const OP_TRANSFORM = 't';
	const OP_PRE_CUT   = 'p';

	private string $url;

	private int                 $quality = 80;
	private string              $secret;
	private readonly Attachment $attachment;

	private array $operations = [];
	private array $operation  = [];

	public function __construct(string $url, string $secret, int $lossyQuality, Attachment $attachment) {
		$this->attachment = $attachment;
		$this->url = $url;
		$this->secret = $secret;
		$this->quality = $lossyQuality;
	}

	#region resize
	public function scale(int $width, int $height): self {
		$this->operation = [self::OP_SCALE => $this->b36($width, $height)];
		return $this;
	}
	public function crop(int $width, int $height): self {
		$this->operation = [self::OP_CROP => $this->b36($width, $height)];
		return $this;
	}
	public function box(int $width, int $height): self {
		$this->operation = [self::OP_BOX => $this->b36($width, $height)];
		return $this;
	}
	public function width(int $width, int $maxHeight = 0): self {
		$this->operation = [self::OP_WIDTH => $this->b36($width, $maxHeight)];
		return $this;
	}
	public function height(int $height, int $maxWidth = 0): self {
		$this->operation = [self::OP_HEIGHT => $this->b36($maxWidth, $height)];
		return $this;
	}
	public function hires($res = 2): static {
		$res = max(1, min($res, 3));
		if ($res !== 1) $this->operations[self::OP_RES] = $res;
		return $this;
	}
	#endregion

	#region export
	public function exportGif(): string { return $this->img('gif'); }
	public function exportPng(): string { return $this->img('png'); }
	public function exportWebp(?int $quality = null): string { return $this->img('webp', is_null($quality) ? $this->quality : $quality); }
	public function exportJpg(?int $quality = null): string { return $this->img('jpg', is_null($quality) ? $this->quality : $quality); }
	public function export(?int $quality = null): string {
		$quality = is_null($quality) ? $this->quality : $quality;
		$pathInfo = pathinfo($this->attachment->file);
		$ext = strtolower($pathInfo['extension']);
		if ($ext == 'jpeg') $ext = 'jpg';
		if ($ext !== 'jpg' && $ext !== 'webp') $quality = null;
		return $this->img($ext, $quality);
	}
	#endregion

	private function img(string $ext, ?int $quality = null): string {
		$this->operations = array_merge($this->operation, $this->operations);
		if (!is_null($transform = $this->attachment->transform) && $transform !== 0) $this->operations[self::OP_TRANSFORM] = $transform;
		if (!is_null($preCut = $this->attachment->crop)) $this->operations[self::OP_PRE_CUT] = $this->b36($preCut->x, $preCut->y, $preCut->width, $preCut->height);
		if (!is_null($safeZone = $this->attachment->safeZone)) $this->operations[self::OP_SAFE_ZONE] = $this->b36($safeZone->x, $safeZone->y, $safeZone->width, $safeZone->height);
		if (!is_null($focus = $this->attachment->focus)) $this->operations[self::OP_FOCUS] = $this->b36($focus->x, $focus->y);
		if (is_int($quality)) $this->operations[self::OP_QUALITY] = min(max($quality, 0), 100);

		$op = '';
		array_walk($this->operations, function ($val, $key) use (&$op) { $op .= '/' . $key . $val; });
		$op = trim($op, '/');
		$file = pathinfo($this->attachment->file, PATHINFO_FILENAME) . '.' . $ext;
		$password = base_convert(crc32($this->attachment->storageId . '/' . $op . '/' . $this->secret . '/' . $file), 10, 36);
		return $this->url . '/' . $this->attachment->storageId . '/' . $op . '/' . $password . '/' . $file;
	}

	private function b36(int $value, int ...$values): string {
		array_unshift($values, $value);
		return join('.', array_map(fn(int $val) => base_convert($val, 10, 36), $values));
	}


	public function __isset($name): bool { return in_array($name, ['png', 'gif', 'jpg', 'jpeg', 'webp', 'url']); }
	public function __get($name): string|null {
		return match ($name) {
			'png'         => $this->exportPng(),
			'gif'         => $this->exportGif(),
			'jpg', 'jpeg' => $this->exportJpg(),
			'webp'        => $this->exportWebp(),
			'url'         => $this->export(),
			default       => null
		};
	}
}