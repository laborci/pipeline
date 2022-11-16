<?php namespace Atomino2\Mercury\Pipeline;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class Context extends ParameterBag {

	public readonly ParameterBag $hostArgs;
	public readonly ParameterBag $pathArgs;
	public readonly Request      $originalRequest;

	public function __construct(Request $originalRequest) {
		parent::__construct();
		$this->originalRequest = $originalRequest;
		$this->hostArgs = new ParameterBag();
		$this->pathArgs = new ParameterBag();
	}
}