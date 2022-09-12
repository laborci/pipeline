<?php namespace Atomino2\Config\Reader;

interface ConfigReaderInterface {
	public function read(string $file): array;
}