<?php namespace Atomino2\Attachment\Img;

use Atomino2\Util\Geometry\Transform;

class ImgCreatorGD2 {

	public function crop(int $width, int $height, string $source, string $target, null|array $preCut, int $transform, int|null $jpegQuality, array|null $safeZone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source, $transform, $preCut))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$aspect = $width / $height;
		$resizeWidth = ($aspect < $oAspect) ? $height * $oAspect : $width;
		$resizeHeight = ($aspect > $oAspect) ? $width / $oAspect : $height;
		$ratio = $resizeWidth / imagesx($img);
		$img = $this->doResize($img, (int)$resizeWidth, (int)$resizeHeight);
		$img = $this->doCrop($img, (int)$width, (int)$height, $safeZone, $focus, $ratio);

		return $this->saveImage($target, $img, $jpegQuality);
	}

	public function height(int $width, int $height, string $source, string $target, null|array $preCut, int $transform, int|null $jpegQuality, array|null $safeZone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source, $transform, $preCut))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$ratio = imagesy($img) / $height;
		$this->doResize($img, (int)($height * $oAspect), $height);
		if ($width != 0 and (int)($height * $oAspect) > $width) return $this->doCrop($img, (int)$width, (int)$height, $safeZone, $focus, $ratio);

		return $this->saveImage($target, $img, $jpegQuality);
	}
	public function width(int $width, int $height, string $source, string $target, null|array $preCut, int $transform, int|null $jpegQuality, array|null $safeZone, array|null $focus): bool {
		if (is_null($img = $this->loadImage($source, $transform, $preCut))) return false;

		$oAspect = imagesx($img) / imagesy($img);
		$ratio = imagesx($img) / $width;

		$img = $this->doResize($img, $width, (int)($width / $oAspect));
		if ($height != 0 and (int)($width / $oAspect) > $height) $img = $this->doCrop($img, (int)$width, (int)$height, $safeZone, $focus, $ratio);
		return $this->saveImage($target, $img, $jpegQuality);
	}

	public function box(int $width, int $height, string $source, null|array $preCut, string $target, int $transform, int|null $jpegQuality): bool {
		if (is_null($img = $this->loadImage($source, $transform, $preCut))) return false;
		$aspect = $width / $height;
		$oAspect = imagesx($img) / imagesy($img);
		if ($aspect < $oAspect) $height = $width / $oAspect;
		elseif ($aspect > $oAspect) $width = $height * $oAspect;
		$img = $this->doResize($img, (int)$width, (int)$height);
		return $this->saveImage($target, $img, $jpegQuality);
	}

	public function scale(int $width, int $height, string $source, string $target, null|array $preCut, int $transform, int|null $jpegQuality): bool {
		if (is_null($img = $this->loadImage($source, $transform, $preCut))) return false;
		$img = $this->doResize($img, (int)$width, (int)$height);
		return $this->saveImage($target, $img, $jpegQuality);
	}

	private function loadImage(string $source, int $transform, $preCut): \GdImage|null {
		$img = match (exif_imagetype($source)) {
			IMAGETYPE_GIF  => imagecreatefromgif($source),
			IMAGETYPE_JPEG => imagecreatefromjpeg($source),
			IMAGETYPE_PNG  => imagecreatefrompng($source),
			IMAGETYPE_WEBP => imagecreatefromwebp($source),
			default        => null
		};
		if ($img === null) return null;

		if (exif_imagetype($source) === IMAGETYPE_JPEG) {
			$exif = @exif_read_data($source);
			if (array_key_exists('Orientation', $exif) && !empty($orientation = $exif['Orientation'])) $img = $this->transform($img, Transform::REVERSE[Transform::EXIF[$orientation]]);
		}
		$img = $this->transform($img, $transform);
		$img = $this->cut($img, $preCut);
		return $img;
	}

	private function saveImage(string $target, \GdImage $img, int|null $jpegQuality): bool {
		$ext = pathinfo($target, PATHINFO_EXTENSION);
		$dir = dirname($target);
		if (!is_dir($dir)) mkdir($dir);
		return match ($ext) {
			'gif'   => imagegif($img, $target),
			'png'   => imagepng($img, $target),
			'jpg'   => imagejpeg($img, $target, $jpegQuality),
			'webp'  => imagewebp($img, $target, $jpegQuality),
			default => false
		};
	}

	private function transform(\GdImage $img, int $transform) {
		[$mirror, $rotate] = Transform::OP[$transform];
		if ($mirror) $img = imageflip($img, IMG_FLIP_HORIZONTAL);
		if ($rotate !== 0) $img = imagerotate($img, $rotate, 0);
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

	private function cut(\GdImage $img, null|array $precut){
		if(is_null($precut)) return $img;
		[$x, $y, $width, $height] = $precut;
		$newImg = imageCreateTrueColor($width, $height);
		imagefill($newImg, 0, 0, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
		imagecopyresampled($newImg, $img, 0, 0, $x, $y, $width, $height, $width, $height);
		imagedestroy($img);
		return $newImg;
	}

	private function doCrop($img, int $width, int $height, array|null $safeZone, array|null $focus, float $ratio): \GdImage|bool {

		$oWidth = imagesx($img);
		$oHeight = imagesy($img);

		if (
			!is_null($safeZone) &&
			!is_null($focus) &&
			(
				$focus[0] < $safeZone[0] ||
				$focus[0] > ($safeZone[0] + $safeZone[2]) ||
				$focus[1] < $safeZone[1] ||
				$focus[1] > ($safeZone[1] + $safeZone[3])
			)
		) $focus = null;

		$safeZone = !is_null($safeZone) ? array_map(fn($n) => $n * $ratio, $safeZone) : [0, 0, $oWidth, $oHeight];
		$focus = !is_null($focus) ? array_map(fn($n) => $n * $ratio, $focus) : [($safeZone[2] - $safeZone[0]) / 2, ($safeZone[3] - $safeZone[1]) / 2];

		$newImg = imageCreateTrueColor($width, $height);
		imagefill($newImg, 0, 0, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
		$sx = $sy = 0;

		if ($oWidth == $width) {
			$sy = $safeZone[1] + $safeZone[3] / 2 - $height / 2;
			if ($safeZone[3] > $height) {
				$sy = $focus[1] - $height / 2;
				if ($sy < $safeZone[1]) $sy = $safeZone[1];
				if (($sy + $height) > ($safeZone[1] + $safeZone[3])) $sy = $safeZone[1] + $safeZone[3] - $height;
				$sy = max(min($sy, $oHeight - $height), 0);
			}
		} else {
			$sx = $safeZone[0] + $safeZone[2] / 2 - $width / 2;
			if ($safeZone[2] > $width) {
				$sx = $focus[0] - $width / 2;
				if ($sx < $safeZone[0]) $sx = $safeZone[0];
				if (($sx + $width) > ($safeZone[0] + $safeZone[2])) $sx = $safeZone[0] + $safeZone[2] - $width;
			}
			$sx = max(min($sx, $oWidth - $width), 0);
		}

		imagecopyresampled($newImg, $img, 0, 0, $sx, $sy, $width, $height, $width, $height);
		imagedestroy($img);
		return $newImg;
	}

}