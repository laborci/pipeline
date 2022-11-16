<?php namespace Atomino2\Application\Config\ValueParser;

interface ConfigValueParserInterface {
	public function parse(string $key, mixed $value): array|false;
}