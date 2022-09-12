<?php namespace Atomino2\Config\Reader;

class INIConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return parse_ini_file($file, false, INI_SCANNER_TYPED); }
}