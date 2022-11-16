<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Mercury\Pipeline\Handler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use \Symfony\Contracts\Cache\CacheInterface;

class Cache extends Handler {
	public function __construct(private CacheInterface $storage) { }

	public function handle(): Response {
		$this->ctx->set(static::class, 0);
		return $this->storage->get(
			crc32($this->originalRequest->getRequestUri()),
			function (ItemInterface $item): null|Response {
				$time = microtime(true);
				$response = $this->next();
				$expiresAfter = $this->ctx->get(static::class, 0);
				if($expiresAfter) debugf("Cache generated in: %sms", round((microtime(true) - $time) * 1000,2));
				$item->expiresAfter($expiresAfter);
				return $response;
			}
		);
	}
}