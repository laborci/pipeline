<?php namespace Atomino2\Config\Reader;

class JSONConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return json_decode(file_get_contents($file), true); }
}