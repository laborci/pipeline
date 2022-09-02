<?php namespace Atomino2;

use DI\Container;

class PhpDIContainer implements DIContainerInterface {
	public function __construct(private Container $di) { }
	public function get(string $id) { return $this->di->get($id); }
	public function has(string $id) { return $this->di->has($id); }
	public function make($name, array $parameters = []) { return $this->di->make($name, $parameters); }
}
