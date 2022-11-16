<?php namespace Atomino\Mercury\Plugins\Attachment;

use Atomino\Bundle\Attachment\AttachmentConfig;
use Atomino\Bundle\Attachment\Config;
use Atomino\Mercury\FileServer\StaticServer;
use Atomino\Mercury\Router\Router;

class AttachmentServer {
	public static function route(Router $router, AttachmentConfig $attachmentConfig) {
		StaticServer::route($router, $attachmentConfig('url') . '/**', $attachmentConfig('path'));
	}
}