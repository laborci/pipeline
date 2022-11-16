<?php namespace Atomino2\Application\Config;

class Config {
	public function __construct(readonly array $values) { }
	public function __invoke(string $key): mixed {
		$value = $this->values;
		foreach (explode('.', $key) as $index) {
			if (!array_key_exists($index, $value)) throw new ConfigException("Config value not found for key:" . $key);
			$value = $value[$index];
		}
		return $value;
	}
}