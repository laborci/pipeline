<?php namespace Atomino2\Config\Reader;

use Atomino\Neutrons\DotNotation;

class DotSeparatedINIConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return DotNotation::extract(parse_ini_file($file, false, INI_SCANNER_TYPED)); }
}