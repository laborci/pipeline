<?php namespace Atomino2\Attachment\Img;

use Atomino2\Attachment\Attachment;

class ImgFactory {
	public function __construct(private readonly string $url, private readonly string $secret, private readonly int $lossyQuality) { }
	public function img(Attachment $attachment): Img { return new Img($this->url, $this->secret, $this->lossyQuality, $attachment); }
}