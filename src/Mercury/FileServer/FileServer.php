<?php namespace Atomino2\Mercury\FileServer;

use Atomino2\Util\PathResolver;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\MimeTypes;

class FileServer {

	public function __construct(
		private readonly Request      $request,
		private readonly PathResolver $pathResolver
	) {
	}

	public function file(string $url, string $file): static {
		if ($this->request->getPathInfo() === $url) {
			$file = ($this->pathResolver)($file);
			$this->serveFile($file);
		}
		return $this;
	}

	public function resolver(FileResolverInterface $resolver): static {
		$result = $resolver->resolve($this->request->getPathInfo());
		if (is_string($result)) $this->serveFile($result);
		return $this;
	}

	public function folder(string $pattern, string $path, \Closure|null $rewrite = null): static {
		$pattern = '/' . trim($pattern, "/") . '/';
		$uri = $this->request->getPathInfo();
		if (str_starts_with($uri, $pattern)) {
			$uri = is_null($rewrite) ? $uri : $rewrite($uri);
			$path = ($this->pathResolver)($path);
			$file = $path . '/' . substr($uri, strlen($pattern));
			$this->serveFile($file);
		}
		return $this;
	}

	protected function serveFile(string $file) {
		if (file_exists($file)) {
			BinaryFileResponse::trustXSendfileTypeHeader();
			$file = new File($file);
			$response = new BinaryFileResponse($file);
			$response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file->getFilename()));
			if (is_array($mimetypes = (new MimeTypes())->getMimeTypes($file->getExtension())) && count($mimetypes)) $response->headers->set('Content-Type', $mimetypes[0]);
			$response->send();
			die();
		}
	}
}