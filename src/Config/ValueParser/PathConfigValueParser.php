<?php namespace Atomino2\Config\ValueParser;

class PathConfigValueParser implements ConfigValueParserInterface {
	public function __construct(private string $root) { }
	public function parse(string $key, mixed $value): array|false {
		if (str_ends_with($key, "@path")) {

			$path = $this->root . '/' . $value;
			array_reduce(explode('/', $path), function ($a, $b) {
				if ($a === null) $a = "/";
				if ($b === "" || $b === ".") return $a;
				if ($b === "..") return dirname($a);
				return preg_replace("/\/+/", "/", "$a/$b");
			});

			return [
				"key"   => substr($key, 0, -5),
				"value" => $path,
			];
		}
		return false;
	}
}