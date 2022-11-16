<?php namespace Atomino2\Application\Config\Reader;

use Atomino2\Util\DotNotation;

class DotSeparatedINIConfigReader implements ConfigReaderInterface {
	public function read(string $file): array { return DotNotation::extract(parse_ini_file($file, false, INI_SCANNER_TYPED)); }
}