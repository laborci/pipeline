<?php namespace Atomino2\Mercury;

use Atomino2\Pipeline\Handler;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;


/**
 * @method Response|null next()
 */
abstract class AbstractRequestHandler extends Handler {
	protected ParameterBag $attributesBag;
	protected ParameterBag $dataBag;
	protected ParameterBag $pathArgsBag;
	protected ParameterBag $hostArgsBag;
	protected FileBag $filesBag;
	protected InputBag $queryBag;
	protected InputBag $cookiesBag;
	protected HeaderBag $headersBag;
	protected ServerBag $serverBag;
	protected false|string|null $content;
	protected InputBag|ParameterBag $postBag;
	protected Request $request;

	public function getAttribute(string $key): mixed { return $this->attributesBag->get($key); }
	public function getData(string $key): mixed { return $this->dataBag->get($key); }
	public function getPathArg(string $key): mixed { return $this->pathArgsBag->get($key); }
	public function getHostArg(string $key): mixed { return $this->hostArgsBag->get($key); }
	public function getFile(string $key): mixed { return $this->filesBag->get($key); }
	public function getQuery(string $key): mixed { return $this->queryBag->get($key); }
	public function getCookie(string $key): mixed { return $this->cookiesBag->get($key); }
	public function getHeader(string $key): mixed { return $this->headersBag->get($key); }
	public function getServer(string $key): mixed { return $this->serverBag->get($key); }
	public function getPost(string $key): mixed { return $this->postBag->get($key); }
	public function getRequest(): Request { return $this->request; }
	public function getContent(): false|string|null { return $this->content; }

	public function run() {

		/** @var Request $request */
		$request = $this->ctx("request");
		$this->request = $request;
		$this->attributesBag = $request->attributes;
		$this->filesBag = $request->files;
		$this->postBag = $request->request;
		$this->queryBag = $request->query;
		$this->cookiesBag = $request->cookies;
		$this->headersBag = $request->headers;
		$this->serverBag = $request->server;
		$this->content = $request->getContent();
		$this->pathArgsBag = $this->ctx("path-args") ?: new ParameterBag();
		$this->hostArgsBag = $this->ctx("host-args") ?: new ParameterBag();
		try {
			$this->dataBag = new ParameterBag($request->toArray());
		} catch (\Exception $e) {
			$this->dataBag = new ParameterBag();
		}
	}

	protected final function redirect($url = '/', $statusCode = 302): never {
		(new RedirectResponse($url, $statusCode))->send();
		die();
	}

	protected function error(int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, string $errorMessage = "", string $errorType = "", string $errorCode = ""): never {
		throw new RequestHandlerErrorException("", 0, null, $statusCode, $errorType, $errorMessage, $errorCode);
	}
}