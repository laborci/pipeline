<?php namespace Atomino2\Attachment\Img;

use Atomino2\Mercury\FileServer\FileResolverInterface;
use DI\Container;

class ImgFileResolver implements FileResolverInterface {

	private Container $di;
	public function __construct(Container $di, private readonly string $url, private readonly string $path, private readonly string $storagePath) {
		$this->di = $di;
	}

	public function resolve($uri): ?string {
		$path = null;
		if (str_starts_with($uri, $this->url)) {
			$uri = trim(substr($uri, strlen($this->url)), '/');
			$path = $this->path . '/' . str_replace('/', '_', $uri);
			debug($path);

			if (!file_exists($path)) {
				$imgResolver = $this->di->get(ImgResolver::class);
				$uriParts = explode('/', $uri);
				$id = array_shift($uriParts);
				$file = array_pop($uriParts);
				$password = array_pop($uriParts);
				$operations = $uriParts;

				$sourcePattern = $this->storagePath . '/' . wordwrap(str_pad($id, 9, '0', STR_PAD_LEFT), 3, '/', true) . '*';
				$source = glob($sourcePattern);
				if (count($source) === 0) return null;
				$source = $source[0];
				if (!$imgResolver->resolve($source, $path, $id, $operations, $password, $file)) $path = null;
			}
		}
		return $path;
	}
}