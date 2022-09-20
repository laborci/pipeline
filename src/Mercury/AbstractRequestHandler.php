<?php namespace Atomino2\Mercury;

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
abstract class AbstractRequestHandler extends \Atomino2\Pipeline\Handler {
	protected ParameterBag $attributes;
	protected ParameterBag $data;
	protected FileBag $files;
	protected InputBag $query;
	protected InputBag $cookies;
	protected HeaderBag $headers;
	protected ServerBag $server;
	protected false|string|null $content;
	protected InputBag|ParameterBag $post;
	protected Request $request;

	public function run() {
		/** @var Request $request */
		$request = $this->ctx("request");
		$this->request = $request;
		$this->attributes = $request->attributes;
		$this->files = $request->files;
		$this->post = $request->request;
		$this->query = $request->query;
		$this->cookies = $request->cookies;
		$this->headers = $request->headers;
		$this->server = $request->server;
		$this->content = $request->getContent();
		try {
			$this->data = new ParameterBag($request->toArray());
		} catch (\Exception $e) {
			$this->data = new ParameterBag();
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