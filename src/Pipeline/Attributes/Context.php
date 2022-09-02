<?php namespace Atomino2\Pipeline\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Context {
	public function __construct(public string|null $name = null) { }
	public static function all(\ReflectionClass|string $reflection): array {
		if (is_string($reflection)) $reflection = new \ReflectionClass($reflection);
		$properties = $reflection->getProperties();
		$attributes = [];
		foreach ($properties as $property) {
			$attrs = $property->getAttributes(static::class);
			if (count($attrs)) {
				$attributes[$property->name] = $attrs[0]->newInstance();
			}
		}
		return $attributes;
	}
}