<?php namespace Atomino\Bundle\Attachment\Img;

use Atomino\Bundle\Attachment\AttachmentConfig;
use JetBrains\PhpStorm\ArrayShape;
use function Atomino\debug;

class ImgResolver {

	public function __construct(private ImgCreatorInterface $creator, private AttachmentConfig $attachmentConfig) { }

	public function resolve(string $url): bool {

		if (!is_dir($this->attachmentConfig["img.path"])) mkdir($this->attachmentConfig["img.path"], 0777, true);

		$uri = explode('/', $url);
		$uri = urldecode(array_pop($uri));
		$target = $this->attachmentConfig["img.path"] . '/' . $uri;
		if (file_exists($target)) return true;

		#region parse uri
		$parts = explode('.', $uri);
		$ext = array_pop($parts);
		$hash = array_pop($parts);
		$path = $pathId = array_pop($parts);
		$jpegQuality = array_pop($parts);
		$opCode = array_pop($parts);
		$op = $this->parseOp($opCode);
		#endregion

		#region source file path
		$file = join('.', $parts);
		$path = substr_replace($path, '/', -6, 0);
		$path = substr_replace($path, '/', -4, 0);
		$path = substr_replace($path, '/', -2, 0);
		$source = ($this->attachmentConfig["path"] .'/'. $path . '/' . $file);
		if (!file_exists($source)) return false;

		#endregion

		#region check hash
		$url = $file . '.' . $opCode . (($jpegQuality) ? ('.' . $jpegQuality) : ('')) . '.' . $pathId . '.' . $ext;
		$newHash = base_convert(crc32($url .$this->attachmentConfig["img.secret"]), 10, 36);
		if ($newHash != $hash) return false;
		#endregion

		$jpegQuality = is_null($jpegQuality) ? null : base_convert($jpegQuality, 36, 10) * 4;

		$result = match ($op['op']) {
			'c' => $this->creator->crop($op['width'], $op['height'], $source, $target, $jpegQuality, $op['safezone'], $op['focus']),
			'h' => $this->creator->height($op['width'], $op['height'], $source, $target, $jpegQuality, $op['safezone'], $op['focus']),
			'w' => $this->creator->width($op['width'], $op['height'], $source, $target, $jpegQuality, $op['safezone'], $op['focus']),
			's' => $this->creator->scale($op['width'], $op['height'], $source, $target, $jpegQuality),
			'b' => $this->creator->box($op['width'], $op['height'], $source, $target, $jpegQuality),
			default => false,
		};
		return !($result === false);
	}

	#[ArrayShape(['op' => "string", 'width' => "int", 'height' => "int", 'safezone' => 'array', 'focus' => 'array'])]
	private function parseOp(string $op): array {
		preg_match('/(?<op>[a-z])(?<arg>[a-z0-9]*)(~(?<safezone>[a-z0-9]*))?(-(?<focus>[a-z0-9]*))?/', $op, $match);
		$argLength = strlen($match['arg']) / 2;
		return [
			'op'       => $match['op'],
			'width'    => $this->bc($match['arg'], 2)[0],
			'height'   => $this->bc($match['arg'], 2)[1],
			'safezone' => array_key_exists('safezone', $match) ? $this->bc($match['safezone'], 4) : null,
			'focus'    => array_key_exists('focus', $match) ? $this->bc($match['focus'], 2) : null,
		];
	}

	private function bc($num, $segments = 1) {
		if ($segments === 1) return (int)base_convert($num, 36, 10);
		$len = strlen($num) / $segments;
		$ret = [];
		for ($i = 0; $i < $segments; $i++) {
			$ret[] = (int)base_convert(substr($num, $i * $len, $len), 36, 10);
		}
		return $ret;
	}


}