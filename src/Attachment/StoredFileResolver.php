<?php namespace Atomino2\Attachment;

use Atomino2\Mercury\FileServer\FileResolverInterface;

class StoredFileResolver implements FileResolverInterface {

	public function __construct(private readonly string $url, private readonly string $path) { }

	public function resolve($uri): ?string {
		if (str_starts_with($uri, $this->url)) {
			$uri = substr($uri, strlen($this->url));
			return $this->path . '/' . preg_replace('/(\/(?!.*\/))/', '.', $uri);
		}
		return null;
	}
}