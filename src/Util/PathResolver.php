<?php namespace Atomino2\Util;

class PathResolver {

	public function __construct(private string $root) { }

	public function __invoke(string ...$path) {
		$path = $this->root . '/' . join("/", $path);
		$realPath = realpath($path);
		return $realPath !== false ? $realPath : $this->realpath($path);
	}

	public function resolve(string ...$path) { return $this(...$path); }

	public function short(string $path) { return substr($path, strlen($this->root)); }

	public function rel($base, $to) {
		$asBase = explode('/', rtrim($base, '/'));
		$arTo = explode('/', rtrim($to, '/'));
		while (count($asBase) && count($arTo) && ($asBase[0] == $arTo[0])) {
			array_shift($asBase);
			array_shift($arTo);
		}
		return str_pad("", count($asBase) * 3, '..' . '/') . implode('/', $arTo);
	}
	public function realpath($path) {
		return array_reduce(explode('/', $path), function ($a, $b) {
			if ($a === null) $a = "/";
			if ($b === "" || $b === ".") return $a;
			if ($b === "..") return dirname($a);
			return preg_replace("/\/+/", "/", "$a/$b");
		});
	}
}