<?php namespace Atomino2\Mercury\Pipeline;

use Atomino2\Mercury\RequestHandlerErrorException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @property-read ParameterBag $args
 * @property-read ParameterBag $pathArgs
 * @property-read ParameterBag $hostArgs
 * @property-read ParameterBag $ctx
 * @property-read ParameterBag $cookies
 * @property-read ParameterBag $headers
 * @property-read ParameterBag $server
 * @property-read ParameterBag $post
 * @property-read ParameterBag $get
 * @property-read ParameterBag $attr
 * @property-read ParameterBag $json
 * @property-read Request $request
 * @property-read Request $originalRequest
 */
abstract class Handler {

	private readonly Context      $context;
	private readonly PipeLine     $pipeline;
	private readonly Request      $request;
	private readonly ParameterBag $args;

	private ?ParameterBag $dataBag = null;

	public function __get(string $name) {
		return match ($name) {
			'args'            => $this->args,
			'pathArgs'        => $this->context->pathArgs,
			'hostArgs'        => $this->context->hostArgs,
			'ctx'             => $this->context,
			'cookies'         => $this->request->cookies,
			'headers'         => $this->request->headers,
			'server'          => $this->request->server,
			'post'            => $this->request->request,
			'get'             => $this->request->query,
			'attr'            => $this->request->attributes,
			'json'            => $this->getRequestContentAsJson(),
			'request'         => $this->request,
			'originalRequest' => $this->context->originalRequest
		};
	}
	private function getRequestContentAsJson(string $key, $default): mixed {
		if (is_null($this->dataBag)) {
			try {
				$this->dataBag = new ParameterBag($this->request->toArray());
			} catch (\Exception $e) {
				$this->dataBag = new ParameterBag();
			}
		}
		return $this->dataBag->get($key, $default);
	}

	abstract public function handle(): ?Response;
	protected final function next(Request|null $request = null): ?Response { return $this->pipeline->next($request); }
	protected final function pipe(string $handler, array $args = []): PipeLine { return $this->pipeline->replace($handler, $args); }
	protected final function stream(array $handlers): PipeLine { return $this->pipeline->replaceStream($handlers); }

	protected final function redirect($url = '/', $statusCode = 302): never {
		(new RedirectResponse($url, $statusCode))->send();
		die();
	}

	protected final function error(int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, string $errorMessage = "", string $errorType = "", string $errorCode = ""): never {
		throw new RequestHandlerErrorException("", 0, null, $statusCode, $errorType, $errorMessage, $errorCode);
	}
}