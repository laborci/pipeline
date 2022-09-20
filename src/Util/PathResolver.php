<?php namespace Atomino2\Util;

class PathResolver {

	public function __construct(private string $root) { }

	public function __invoke(string ...$path) {
		$path = $this->root . '/' . join("/", $path);
		$realPath = realpath($path);
		return $realPath !== false ? $realPath : $this->resolveNonExisting($path);
	}

	public function resolve(string ...$path) { return $this(...$path); }

	private function resolveNonExisting($path) {
		array_reduce(explode('/', $path), function ($a, $b) {
			if ($a === null) $a = "/";
			if ($b === "" || $b === ".") return $a;
			if ($b === "..") return dirname($a);
			return preg_replace("/\/+/", "/", "$a/$b");
		});
		return $path;
	}
}