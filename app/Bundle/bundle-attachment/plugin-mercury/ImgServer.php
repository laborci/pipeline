<?php namespace Atomino\Mercury\Plugins\Attachment;

use Atomino\Bundle\Attachment\AttachmentConfig;
use Atomino\Bundle\Attachment\Config;
use Atomino\Bundle\Attachment\Img\ImgResolver;
use Atomino\Core\ApplicationConfig;
use Atomino\Mercury\FileServer\FileLocator;
use Atomino\Mercury\FileServer\FileServer;
use Atomino\Mercury\Pipeline\Handler;
use Atomino\Mercury\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Atomino\debug;
use function Atomino\dic;

class ImgServer extends Handler {

	public function __construct(
		private ApplicationConfig $config,
		private ImgResolver $imgResolver
	) { }

	public static function route(Router $router, AttachmentConfig $attachmentConfig) {
		$router(method: 'GET', path: $attachmentConfig("img.url") . '/**')
			?->pipe(ImgServer::class)
		     ->pipe(...FileLocator::setup($attachmentConfig("img.path")))
		     ->pipe(FileServer::class)
		;
	}

	public function handle(Request $request): Response|null {
		$result = $this->imgResolver->resolve($request->getPathInfo());
		return $result ? $this->next($request) : null;
	}
}