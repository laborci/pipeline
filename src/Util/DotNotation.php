<?php namespace Atomino2\Util;

class DotNotation {
	public static function extract(array $array): array {
		$flattened = self::flatten($array);
		$result = [];
		foreach ($flattened as $key => $value) self::set($result, $key, $value);
		return $result;
	}

	private static function set(&$array, $key, $value) {
		if (is_null($key)) return $array = $value;
		$keys = explode('.', $key);
		while (count($keys) > 1) {
			$key = array_shift($keys);
			if (!isset($array[$key]) || !is_array($array[$key])) $array[$key] = [];
			$array =& $array[$key];
		}
		$array[array_shift($keys)] = $value;
		return $array;
	}

	public static function flatten($array, $prepend = '') {
		$results = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$results = array_merge($results, self::flatten($value, $prepend . $key . '.'));
			} else {
				$results[$prepend . $key] = $value;
			}
		}
		return $results;
	}
	

}