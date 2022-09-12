<?php namespace Atomino2\Config\ValueParser;

interface ConfigValueParserInterface {
	public function parse (string $key, mixed $value):array|false;
}