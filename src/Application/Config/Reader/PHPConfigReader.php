<?php namespace Atomino2\Application\Config\Reader;

class PHPConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return include $file; }
}