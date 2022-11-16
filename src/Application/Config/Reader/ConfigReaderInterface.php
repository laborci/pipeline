<?php namespace Atomino2\Application\Config\Reader;

interface ConfigReaderInterface {
	public function read(string $file): array;
}