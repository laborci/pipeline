<?php namespace Atomino2\Util;

abstract class Attr {

	/**
	 * @param \ReflectionClass|\ReflectionMethod|string $reflection
	 * @param string|null $method
	 * @return static|null
	 * @throws \ReflectionException
	 */
	public static function get(\ReflectionClass|\ReflectionMethod|string $reflection, string|null $method = null): static|null {
		$attributes = static::getAttributes($reflection, $method);
		if (count($attributes) === 0) return null;
		/** @var static $instance */
		return $attributes[0]->newInstance();
	}

	/**
	 * @param \ReflectionClass|\ReflectionMethod|string $reflection
	 * @param string|null $method
	 * @return static[]
	 * @throws \ReflectionException
	 */
	public static function all(\ReflectionClass|\ReflectionMethod|string $reflection, string|null $method = null): array {
		$attributes = static::getAttributes($reflection, $method);
		return static::instantiateAttributes($attributes);
	}

	/**
	 * @param \ReflectionClass|\ReflectionMethod ...$reflections
	 * @return static[]
	 */
	public static function collect(\ReflectionClass|\ReflectionMethod ...$reflections): array {
		/** @var static[] $attributes */
		$attributes = [];
		foreach ($reflections as $reflection) array_push($attributes, ... $reflection->getAttributes(static::class));
		return static::instantiateAttributes($attributes);
	}

	/**
	 * @param \ReflectionClass|\ReflectionMethod|string $reflection
	 * @param string|null $method
	 * @return \ReflectionAttribute[]
	 * @throws \ReflectionException
	 */
	private static function getAttributes(\ReflectionClass|\ReflectionMethod|string $reflection, string|null $method = null): array {
		if (is_string($reflection)) $reflection = new \ReflectionClass($reflection);
		if (is_string($method) && $reflection instanceof \ReflectionClass) $reflection = $reflection->getMethod($method);
		return $reflection->getAttributes(static::class);
	}

	/**
	 * @param \ReflectionAttribute[] $attributes
	 * @return static[]
	 */
	private static function instantiateAttributes(array $attributes): array {
		return array_map(function (\ReflectionAttribute $attribute): static { return $attribute->newInstance(); }, $attributes);
	}
}
