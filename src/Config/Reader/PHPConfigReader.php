<?php namespace Atomino2\Config\Reader;

class PHPConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return include $file; }
}