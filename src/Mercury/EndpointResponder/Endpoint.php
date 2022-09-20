<?php namespace Atomino2\Mercury\EndpointResponder;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Endpoint {
	public const HEAD = 'HEAD';
	public const GET = 'GET';
	public const POST = 'POST';
	public const PUT = 'PUT';
	public const PATCH = 'PATCH';
	public const DELETE = 'DELETE';
	public const PURGE = 'PURGE';
	public const OPTIONS = 'OPTIONS';
	public const TRACE = 'TRACE';
	public const CONNECT = 'CONNECT';

	public array|null $methods;
	public function __construct(public string $route, string ...$methods) {
		$this->methods = count($methods) ? $methods : null;
	}
}