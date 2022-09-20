<?php namespace Atomino2\Config\ValueParser;

use Atomino2\Util\PathResolver;

class PathConfigValueParser implements ConfigValueParserInterface {

	private PathResolver $pathResolver;

	public function __construct(string $root) {
		$this->pathResolver = new PathResolver($root);
	}

	public function parse(string $key, mixed $value): array|false {
		if (str_ends_with($key, "@path")) {
			return [
				"key"   => substr($key, 0, -5),
				"value" => ($this->pathResolver)($value),
			];
		}
		return false;
	}
}