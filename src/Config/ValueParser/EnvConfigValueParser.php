<?php namespace Atomino2\Config\ValueParser;

use Atomino2\Config\ConfigException;

class EnvConfigValueParser implements ConfigValueParserInterface {
	public function parse(string $key, mixed $value): array|false {
		if (str_ends_with($key, '@env')) {
			return [
				"key"   => substr($key, 0, -4),
				"value" => $this->getenv($value),
			];
		} elseif (str_ends_with($key, '@env-bool')) {
			$key = substr($key, 0, -9);
			if (is_string($value)) {
				$value = strtolower($this->getenv($value));
				return [
					"key"   => $key,
					"value" => in_array($value, ['yes', 'true']),
				];
			} else {
				return [
					"key"   => $key,
					"value" => boolval($this->getenv($value)),
				];
			}
		} elseif (str_ends_with($key, "@env-int") || str_ends_with($key, "@env-num")) {
			return [
				"key"   => substr($key, 0, -8),
				"value" => intval($this->getenv($value)),
			];
		} elseif (str_ends_with($key, "@env-float")) {
			return [
				"key"   => substr($key, 0, -10),
				"value" => floatval($this->getenv($value)),
			];
		}
		return false;
	}

	private function getenv($key){
		if(!array_key_exists($key, $_ENV)) throw new ConfigException("ENV not found:".$key);
		else return trim($_ENV[$key]);
	}
}