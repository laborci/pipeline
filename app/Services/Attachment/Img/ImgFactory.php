<?php namespace App\Services\Attachment\Img;

use App\Services\Attachment\Attachment;

class ImgFactory {
	public function __construct(private readonly string $url, private readonly string $secret, private readonly int $lossyQuality) { }
	public function img(Attachment $attachment): Img { return new Img($this->url, $this->secret, $this->lossyQuality, $attachment); }
}