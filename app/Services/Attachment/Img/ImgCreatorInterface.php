<?php namespace App\Services\Attachment\Img;

interface ImgCreatorInterface {
	public function getDimensions($file): array;
	public function crop(int $width, int $height, string $source, string $target, int $jpegQuality, array|null $safezone, array|null $focus): bool;
	public function height(int $width, int $height, string $source, string $target, int $jpegQuality, array|null $safezone, array|null $focus): bool;
	public function width(int $width, int $height, string $source, string $target, int $jpegQuality, array|null $safezone, array|null $focus): bool;
	public function box(int $width, int $height, string $source, string $target, int $jpegQuality): bool;
	public function scale(int $width, int $height, string $source, string $target, int $jpegQuality): bool;
}