<?php namespace Atomino\Bundle\Attachment\Img;

use function Atomino\debug;

class ImgCreatorGD2 implements ImgCreatorInterface {

	public function getDimensions($file): array {
		$img = $this->loadImage($file);
		return [
			"width"  => imagesx($img),
			"height" => imagesy($img),
		];
	}

	public function crop(int $width, int $height, string $source, string $target, int|null $jpegQuality, array|null $safezone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$aspect = $width / $height;
		$resizeWidth = ($aspect < $oAspect) ? $height * $oAspect : $width;
		$resizeHeight = ($aspect > $oAspect) ? $width / $oAspect : $height;
		$ratio = $resizeWidth / imagesx($img);
		$img = $this->doResize($img, (int)$resizeWidth, (int)$resizeHeight);
		$img = $this->doCrop($img, (int)$width, (int)$height, $safezone, $focus, $ratio);

		return $this->saveImage($target, $img, $jpegQuality);
	}

	public function height(int $width, int $height, string $source, string $target, int|null $jpegQuality, array|null $safezone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$ratio = imagesy($img) / $height;
		$this->doResize($img, (int)($height * $oAspect), $height);
		if ($width != 0 and (int)($height * $oAspect) > $width) return $this->doCrop($img, (int)$width, (int)$height, $safezone, $focus, $ratio);

		return $this->saveImage($target, $img, $jpegQuality);
	}
	public function width(int $width, int $height, string $source, string $target, int|null $jpegQuality, array|null $safezone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$ratio = imagesx($img) / $width;

		$img = $this->doResize($img, $width, (int)($width / $oAspect));
		if ($height != 0 and (int)($width / $oAspect) > $height) $img = $this->doCrop($img, (int)$width, (int)$height, $safezone, $focus, $ratio);
		return $this->saveImage($target, $img, $jpegQuality);
	}


	public function box(int $width, int $height, string $source, string $target, int|null $jpegQuality): bool {
		if (is_null($img = $this->loadImage($source))) return false;
		$aspect = $width / $height;
		$oAspect = imagesx($img) / imagesy($img);
		if ($aspect < $oAspect) $height = (int)$width / $oAspect;
		elseif ($aspect > $oAspect) $width = (int)$height * $oAspect;
		return $this->saveImage($target, $img, $jpegQuality);
	}

	public function scale(int $width, int $height, string $source, string $target, int|null $jpegQuality): bool {
		if (is_null($img = $this->loadImage($source))) return false;
		$img = $this->doResize($img, (int)$width, (int)$height);
		return $this->saveImage($target, $img, $jpegQuality);
	}

	private function loadImage(string $source): \GdImage|null {
		return $this->prepareOrientation($source);
	}

	private function saveImage(string $target, \GdImage $img, int|null $jpegQuality): bool {
		$pathInfo = pathinfo($target);
		$dir = dirname($target);
		if(!is_dir($dir)) mkdir($dir);
		return match ($pathInfo['extension']) {
			'gif' => imagegif($img, $target),
			'png' => imagepng($img, $target),
			'jpg' => imagejpeg($img, $target, $jpegQuality),
			'webp' => imagewebp($img, $target, $jpegQuality),
			default => false
		};
	}

	private function prepareOrientation(string $file): \GdImage|null {
		$img = match (exif_imagetype($file)) {
			IMAGETYPE_GIF => imagecreatefromgif($file),
			IMAGETYPE_JPEG => imagecreatefromjpeg($file),
			IMAGETYPE_PNG => imagecreatefrompng($file),
			IMAGETYPE_WEBP => imagecreatefromwebp($file),
			default => null
		};
		if ($img === null) return null;

		if(exif_imagetype($file) === IMAGETYPE_JPEG) {
			$exif = @exif_read_data($file);
			if (!empty($exif['Orientation'])) {
				$deg = match ($exif["Orientation"]) {
					8 => 90,
					3 => 180,
					6 => -90,
					default => 0
				};
				if ($deg !== 0) $img = imagerotate($img, $deg, 0);
			}
		}
		return $img;
	}

	private function doResize($img, int $width, int $height): \GdImage|bool {
		$newImg = imagecreatetruecolor($width, $height);
		$oWidth = imagesx($img);
		$oHeight = imagesy($img);
		imagefill($newImg, 0, 0, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
		imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);
		imagedestroy($img);
		return $newImg;
	}

	private function doCrop($img, int $width, int $height, array|null $safezone, array|null $focus, float $ratio): \GdImage|bool {

		$oWidth = imagesx($img);
		$oHeight = imagesy($img);

		if (!is_null($safezone) && !is_null($focus)) {
			if ($focus[0] < $safezone[0] || $focus[0] > ($safezone[0] + $safezone[2]) || $focus[1] < $safezone[1] || $focus[1] > ($safezone[1] + $safezone[3])) $focus = null;
		}

		if (!is_null($safezone)) {
			$safezone = array_map(fn($n) => $n * $ratio, $safezone);
		} else {
			$safezone = [0, 0, $oWidth, $oHeight];
		}
		if (!is_null($focus)) {
			$focus = array_map(fn($n) => $n * $ratio, $focus);
		} else {
			$focus = [
				($safezone[2] - $safezone[0]) / 2,
				($safezone[3] - $safezone[1]) / 2,
			];
		}


		$newImg = imageCreateTrueColor($width, $height);
		imagefill($newImg, 0, 0, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
		$sx = $sy = 0;


		if ($oWidth == $width) {
			$sy = $safezone[1] + $safezone[3] / 2 - $height / 2;
			if ($safezone[3] > $height) {
				$sy = $focus[1] - $height / 2;
				if ($sy < $safezone[1]) $sy = $safezone[1];
				if (($sy + $height) > ($safezone[1] + $safezone[3])) $sy = $safezone[1] + $safezone[3] - $height;
				$sy = max(min($sy, $oHeight - $height), 0);
			}
		} else {
			$sx = $safezone[0] + $safezone[2] / 2 - $width / 2;
			if ($safezone[2] > $width) {
				$sx = $focus[0] - $width / 2;
				if ($sx < $safezone[0]) $sx = $safezone[0];
				if (($sx + $width) > ($safezone[0] + $safezone[2])) $sx = $safezone[0] + $safezone[2] - $width;
			}
			$sx = max(min($sx, $oWidth - $width), 0);
		}

		imagecopyresampled($newImg, $img, 0, 0, $sx, $sy, $width, $height, $width, $height);
		imagedestroy($img);
		return $newImg;
	}

}